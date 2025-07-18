<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Kategori;
use App\Models\BarangMasuk;
use App\Models\SubKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreBarangMasukRequest;
use App\Http\Requests\UpdateBarangMasukRequest;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pagination = $request->input('pagination', 10);
        // filter by tahung tanggal
        $tanggal = $request->input('tanggal', null);

        // filter by kategori
        $kategoriId = $request->input('kategori_id', null);
        // filter by subkategori
        $subKategoriId = $request->input('sub_kategori_id', null);



        $barangMasuk = BarangMasuk::with(['user', 'subKategori', 'barang'])
            ->when($tanggal, function ($query) use ($tanggal) {
                return $query->whereDate('tanggal_masuk', $tanggal);
            })
            ->when($kategoriId, function ($query) use ($kategoriId) {
                return $query->whereHas('subKategori.kategori', function ($q) use ($kategoriId) {
                    $q->where('id', $kategoriId);
                });
            })
            ->when($subKategoriId, function ($query) use ($subKategoriId) {
                return $query->where('sub_kategori_id', $subKategoriId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($pagination);
        // dd($barangMasuk->toArray());
        // Transformasi data dalam pagination
        $itemsview = $barangMasuk->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'tanggal' => $item->tanggal_masuk,
                'asal_barang' => $item->asal_barang,
                'penerima' => optional($item->user)->name,
                'unit' => optional($item->subKategori)->nama_sub_kategori,
                'items' => $item->barang->map(function ($barang) {
                    return [
                        'kode' => $barang->id,
                        'nama' => $barang->nama_barang,
                        'jumlah' => "{$barang->jumlah_barang} {$barang->satuan_barang}",
                        'harga' => $barang->harga_barang,
                        'total' => $barang->total_harga,
                        'tanggal_expired' => $barang->tanggal_expired
                    ];
                }),
            ];
        });
        // dd($barangMasuk->toArray());
        // Jika hanya untuk debug:
        // dd($barangMasuk);
        return Inertia::render('BarangMasuk/Index', [
            'items' => $itemsview,
            'pagination' => $barangMasuk,
            'meta' => [
                'total' => $barangMasuk->total(),
                'from' => $barangMasuk->firstItem(),
                'to' => $barangMasuk->lastItem(),
            ],
            'filters' => [
                'tanggal' => $tanggal,
                'kategori_id' => $kategoriId,
                'sub_kategori_id' => $subKategoriId,

            ],
            'kategori' => Kategori::with('subKategoris')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // if (Auth::user()->role_name !== 'admin') {
        //     // operator hanya bisa melihat operator yang ada
        //     // $operator = User::where('role_name', 'operator')
        //     //     ->where('id', Auth::user()->id)
        //     //     ->get();
        // } else {
        $operator = User::where('role_name', 'operator')->get();
        // }
        $kategori = Kategori::all();

        // subkategori
        $subKategori = SubKategori::all();

        return Inertia::render('BarangMasuk/Create', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'barangMasuk' => new BarangMasuk(),
            'operators' => $operator->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'username' => $item->username,
                    'email' => $item->email,
                ];
            })->toArray(),
            'kategoris' => $kategori->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_kategori' => $item->nama_kategori,
                ];
            })->toArray(),
            'sub_kategoris' => $subKategori->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_sub_kategori' => $item->nama_sub_kategori,
                    'kategori_id' => $item->kategori_id,
                    'batas_harga' => $item->batas_harga,
                ];
            })->toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'tanggal_masuk' => 'required|date',
            'asal_barang' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'sub_kategori_id' => 'required|exists:sub_kategoris,id',
            'nomor_surat' => 'nullable|string|max:255',
            'batas_harga' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // Tambahkan validasi untuk lampiran
            'barang' => 'required|array',
            'barang.*.nama' => 'required|string|max:255',
            'barang.*.harga' => 'required|numeric|min:0',
            'barang.*.jumlah' => 'required|integer|min:1',
            'barang.*.satuan' => 'nullable|string|max:50',
            'barang.*.total' => 'nullable|numeric|min:0',
            'barang.*.expired' => 'nullable|date',
        ]);

        // Jika ada lampiran, simpan file-nya
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('lampiran', 'public');
            $request->merge(['lampiran' => $lampiranPath]);
        } else {
            $request->merge(['lampiran' => null]);
        }
        // dd($request->all());
        // Hentikan jika validasi awal gagal
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Validasi tambahan: harga barang tidak boleh melebihi batas_harga
        $errors = [];

        foreach ($data['barang'] as $index => $item) {
            if ($item['harga'] > $data['batas_harga']) {
                $errors["barang.$index.harga"] = "Harga tidak boleh melebihi batas harga ({$data['batas_harga']})";
            }
        }

        // Jika ada error tambahan, kembalikan
        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        DB::beginTransaction();

        try {
            // Create BarangMasuk record
            $barangMasuk = BarangMasuk::create([
                'tanggal_masuk' => $data['tanggal_masuk'],
                'asal_barang' => $data['asal_barang'],
                'user_id' => $data['user_id'],
                'sub_kategori_id' => $data['sub_kategori_id'],
                'nomor_surat' => $data['nomor_surat'] ?? null,
            ]);

            // Loop through barang and create related records
            $items = collect($data['barang'])->map(function ($item) use ($barangMasuk) {
                return [
                    'barang_masuk_id' => $barangMasuk->id,
                    'nama_barang' => $item['nama'],
                    'harga_barang' => $item['harga'],
                    'jumlah_barang' => $item['jumlah'],
                    'satuan_barang' => $item['satuan'] ?? 'pcs',
                    'total_harga' => $item['total'] ?? ($item['harga'] * $item['jumlah']),
                    'tanggal_expired' => $item['expired'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            // dd($items);
            Barang::insert($items);


            DB::commit();
            // Redirect to index with success message
            return redirect()->route('barang-masuk.index')->with('success', __('Barang masuk berhasil ditambahkan.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
        return redirect()->route('barang-masuk.index')->with('success', __('Barang masuk berhasil ditambahkan.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(BarangMasuk $barangMasuk)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BarangMasuk $barangMasuk)
    {
        // if (Auth::user()->role_name !== 'admin') {
        //     // operator hanya bisa melihat operator yang ada
        //     // $operator = User::where('role_name', 'operator')
        //     //     ->where('id', Auth::user()->id)
        //     //     ->get();
        // } else {
        $operator = User::where('role_name', 'operator')->get();
        // }
        $kategori = Kategori::all();

        // subkategori
        $subKategori = SubKategori::all();
        $barangMasuk->load(['user', 'subKategori', 'barang']);
        // Transformasi data barangMasuk untuk Inertia
        $barangmasuk = [
            'id' => $barangMasuk->id,
            'tanggal_masuk' => $barangMasuk->tanggal_masuk,
            'asal_barang' => $barangMasuk->asal_barang,
            'user_id' => $barangMasuk->user_id,
            'kategori_id' => $barangMasuk->subKategori ? $barangMasuk->subKategori->kategori_id : null,
            'sub_kategori_id' => $barangMasuk->sub_kategori_id,
            'nomor_surat' => $barangMasuk->nomor_surat,
            'batas_harga' => $barangMasuk->subKategori ? $barangMasuk->subKategori->batas_harga : null,
            'barang' => $barangMasuk->barang->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'harga_barang' => $item->harga_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'satuan_barang' => $item->satuan_barang,
                    'total_harga' => $item->total_harga,
                    'tanggal_expired' => $item->tanggal_expired,
                ];
            })->toArray(),
        ];

        // dd($barangmasuk);

        return Inertia::render('BarangMasuk/Edit', [
            'auth' => [
                'user' => Auth::user(),
            ],
            'barangMasuk' => new BarangMasuk(),
            'operators' => $operator->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'username' => $item->username,
                    'email' => $item->email,
                ];
            })->toArray(),
            'kategoris' => $kategori->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_kategori' => $item->nama_kategori,
                ];
            })->toArray(),
            'sub_kategoris' => $subKategori->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_sub_kategori' => $item->nama_sub_kategori,
                    'kategori_id' => $item->kategori_id,
                    'batas_harga' => $item->batas_harga,
                ];
            })->toArray(),
            'barangMasuk' => $barangmasuk
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $barangMasuk = BarangMasuk::findOrFail($id);
        // dd($request->all());
        // dd($barangMasuk);
        $validator = Validator::make($request->all(), [
            'tanggal_masuk' => 'required|date',
            'asal_barang' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'sub_kategori_id' => 'required|exists:sub_kategoris,id',
            'nomor_surat' => 'nullable|string|max:255',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'batas_harga' => 'required|numeric|min:0',
            'barang' => 'required|array',
            'barang.*.id' => 'nullable|exists:barangs,id', // Untuk update barang yang sudah ada
            'barang.*.nama' => 'required|string|max:255',
            'barang.*.harga' => 'required|numeric|min:0',
            'barang.*.jumlah' => 'required|integer|min:1',
            'barang.*.satuan' => 'nullable|string|max:50',
            'barang.*.total' => 'nullable|numeric|min:0',
            'barang.*.tanggal_expired' => 'nullable|date',
        ]);

        // Jika ada lampiran, simpan file-nya
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('lampiran', 'public');
            $request->merge(['lampiran' => $lampiranPath]);
        } else {
            $request->merge(['lampiran' => null]);
        }

        // Hentikan jika validasi awal gagal
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Validasi tambahan: harga barang tidak boleh melebihi batas_harga
        $errors = [];

        foreach ($data['barang'] as $index => $item) {
            if ($item['harga'] > $data['batas_harga']) {
                $errors["barang.$index.harga"] = "Harga tidak boleh melebihi batas harga ({$data['batas_harga']})";
            }
        }

        // Jika ada error tambahan, kembalikan
        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        DB::beginTransaction();

        try {
            // Update BarangMasuk record
            $barangMasuk->update([
                'tanggal_masuk' => $data['tanggal_masuk'],
                'asal_barang' => $data['asal_barang'],
                'user_id' => $data['user_id'],
                'sub_kategori_id' => $data['sub_kategori_id'],
                'nomor_surat' => $data['nomor_surat'] ?? null,
            ]);

            // Hapus semua barang lama yang terkait dengan barang masuk ini
            $barangMasuk->barang()->delete();

            // Insert ulang semua barang baru dari form
            foreach ($data['barang'] as $item) {
                $barangMasuk->barang()->create([
                    'nama_barang' => $item['nama'],
                    'harga_barang' => $item['harga'],
                    'jumlah_barang' => $item['jumlah'],
                    'satuan_barang' => $item['satuan'] ?? 'pcs',
                    'total_harga' => $item['total'] ?? ($item['harga'] * $item['jumlah']),
                    'tanggal_expired' => $item['expired'] ?? null, // pastikan key sama dengan form
                ]);
            }


            DB::commit();

            return redirect()->route('barang-masuk.index')->with('success', __('Barang masuk berhasil diperbarui.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BarangMasuk $barangMasuk)
    {
        //
        // Hapus barang terkait
        $barangMasuk->barang()->delete();
        // Hapus barang masuk
        $barangMasuk->delete();
        return redirect()->route('barang-masuk.index')->with('success', __('Barang masuk berhasil dihapus.'));
    }
}
