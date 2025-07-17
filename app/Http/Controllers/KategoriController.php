<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $items = Kategori::orderBy('created_at', 'desc')->paginate($perPage);

        $column = [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'number',
                'headerClass' => 'w-20'
            ],
            [
                'key' => 'kode_kategori',
                'label' => 'Kode Kategori',
                'type' => 'number',
                'headerClass' => 'w-20'
            ],
            [
                'key' => 'nama_kategori',
                'label' => 'Nama Kategori',
                'type' => 'text',
                'headerClass' => 'w-1/3'
            ],
        ];

        $ItemView = $items->map(function ($kategori) {
            return [
                'id' => $kategori->id,
                'kode_kategori' => $kategori->kode_kategori,
                'nama_kategori' => $kategori->nama_kategori,
            ];
        });
        // Return the index view with the paginated items
        return Inertia::render('Kategori/Index', [
            'items' => $items,
            'ItemView' => $ItemView,
            'columns' => $column,
            'filters' => $request->all('search', 'trashed'),
            'perPage' => $perPage,
            'meta' => [
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKategoriRequest $request)
    {
        // Validate the request
        $validatedData = $request->validated();
        // Create a new Kategori instance
        Kategori::create([
            'kode_kategori' => $validatedData['kode_kategori'],
            'nama_kategori' => $validatedData['nama_kategori'],
        ]);
        // Redirect back to the index with a success message
        return redirect()->route('kategori.index')->with('success', 'Kategori created successfully.');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKategoriRequest $request, Kategori $kategori)
    {
        // Validate the request
        $validatedData = $request->validated();
        // Update the Kategori instance
        $kategori->update([
            'kode_kategori' => $validatedData['kode_kategori'],
            'nama_kategori' => $validatedData['nama_kategori'],
        ]);
        // Redirect back to the index with a success message
        return redirect()->route('kategori.index')->with('success', 'Kategori updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kategori $kategori)
    {
        //  Delete the Kategori instance
        $kategori->delete();
        // Redirect back to the index with a success message
        return redirect()->route('kategori.index')->with('success', 'Kategori deleted successfully.');
    }
}
