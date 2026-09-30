<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HospitalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->trim()->toString();

        $hospitals = Hospital::query()
            ->with('contacts')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.hospitals.index', compact('hospitals', 'search'));
    }

    public function create()
    {
        return view('admin.hospitals.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $contacts = $data['contacts'];
        unset($data['contacts']);

        if ($request->hasFile('pks')) {
            $data['pks_path'] = $request->file('pks')->store('pks', config('hasnurverif.pks.disk'));
        }

        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
        $data['pic_phone'] = $contacts[0]['phone'] ?? null;

        $hospital = Hospital::create($data);
        $this->syncContacts($hospital, $contacts);

        return redirect()->route('admin.hospitals.index')
            ->with('success', 'Data RS berhasil ditambahkan.');
    }

    public function edit(Hospital $hospital)
    {
        $hospital->load('contacts');

        return view('admin.hospitals.edit', compact('hospital'));
    }

    public function update(Request $request, Hospital $hospital)
    {
        $data = $this->validated($request, $hospital->id);
        $contacts = $data['contacts'];
        unset($data['contacts']);

        if ($request->hasFile('pks')) {
            if ($hospital->pks_path) {
                Storage::disk(config('hasnurverif.pks.disk'))->delete($hospital->pks_path);
            }
            $data['pks_path'] = $request->file('pks')->store('pks', config('hasnurverif.pks.disk'));
        }

        $data['updated_by'] = $request->user()->id;
        $data['pic_phone'] = $contacts[0]['phone'] ?? null;

        $hospital->update($data);
        $this->syncContacts($hospital, $contacts);

        return redirect()->route('admin.hospitals.index')
            ->with('success', 'Data RS berhasil diperbarui.');
    }

    public function destroy(Hospital $hospital)
    {
        $hospital->delete();

        return redirect()->route('admin.hospitals.index')
            ->with('success', 'Data RS berhasil dihapus.');
    }

    /**
     * Tampilkan PKS (inline) agar dapat dilihat langsung di browser.
     */
    public function pks(Hospital $hospital)
    {
        return $this->pksResponse($hospital, 'inline');
    }

    /**
     * Unduh PKS sebagai attachment.
     */
    public function pksDownload(Hospital $hospital)
    {
        return $this->pksResponse($hospital, 'attachment');
    }

    /**
     * Hapus PKS RS (mis. salah upload). Setelah dihapus, tombol Lihat PKS
     * di halaman RS ikut hilang.
     */
    public function pksDestroy(Request $request, Hospital $hospital)
    {
        if ($hospital->pks_path) {
            Storage::disk(config('hasnurverif.pks.disk'))->delete($hospital->pks_path);
        }

        $hospital->update([
            'pks_path' => null,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.hospitals.edit', $hospital)
            ->with('success', 'PKS berhasil dihapus.');
    }

    protected function pksResponse(Hospital $hospital, string $disposition)
    {
        $disk = Storage::disk(config('hasnurverif.pks.disk'));

        if (! $hospital->pks_path || ! $disk->exists($hospital->pks_path)) {
            abort(404);
        }

        $filename = 'PKS-'.$hospital->slug.'.pdf';

        return response()->file($disk->path($hospital->pks_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Sinkronkan kontak RS tanpa mengubah ID yang sudah ada, agar sesi RS
     * yang sedang aktif (menyimpan hospital_contact_id) tetap valid.
     *
     * @param  array<int, array{id?: mixed, label?: string, phone: string}>  $contacts
     */
    protected function syncContacts(Hospital $hospital, array $contacts): void
    {
        $existingIds = $hospital->contacts()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $keptIds = [];

        foreach ($contacts as $contact) {
            $normalized = Hospital::normalizePhone($contact['phone'] ?? null);

            if (! $normalized) {
                continue;
            }

            $id = isset($contact['id']) && $contact['id'] !== '' ? (int) $contact['id'] : null;

            if ($id && in_array($id, $existingIds, true)) {
                $hospital->contacts()->whereKey($id)->update([
                    'label' => $contact['label'] ?? null,
                    'phone' => $contact['phone'],
                    'phone_normalized' => $normalized,
                    'is_active' => true,
                ]);
                $keptIds[] = $id;
            } else {
                $created = $hospital->contacts()->create([
                    'label' => $contact['label'] ?? null,
                    'phone' => $contact['phone'],
                    'phone_normalized' => $normalized,
                    'is_active' => true,
                ]);
                $keptIds[] = (int) $created->id;
            }
        }

        $hospital->contacts()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('name')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required', 'string', 'max:100', 'alpha_dash',
                Rule::unique('hospitals', 'slug')->ignore($ignoreId),
            ],
            'detail' => ['nullable', 'string', 'max:2000'],
            'pks' => ['nullable', 'file', 'mimes:pdf', 'max:'.config('hasnurverif.pks.max_size_kb')],
            'is_active' => ['nullable', 'boolean'],
            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.id' => ['nullable', 'integer'],
            'contacts.*.label' => ['nullable', 'string', 'max:100'],
            'contacts.*.phone' => ['required', 'string', 'max:25'],
        ], [
            'contacts.required' => 'Minimal satu nomor WhatsApp PIC harus diisi.',
            'contacts.*.phone.required' => 'Nomor WhatsApp PIC wajib diisi.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        unset($data['pks']);

        return $data;
    }
}
