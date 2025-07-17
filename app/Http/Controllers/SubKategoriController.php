<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\SubKategori;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSubKategoriRequest;
use App\Http\Requests\UpdateSubKategoriRequest;
use App\Models\Kategori;

class SubKategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $items = SubKategori::orderBy('created_at', 'desc')->with('kategori')->paginate($perPage);

        $column = [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'number',
                'headerClass' => 'w-20'
            ],
            [
                'key' => 'nama_kategori',
                'label' => 'Nama Kategori',
                'type' => 'text',
                'headerClass' => 'w-1/3'
            ],
            [
                'key' => 'nama_sub_kategori',
                'label' => 'Nama Sub Kategori',
                'type' => 'text',
                'headerClass' => 'w-1/3'
            ],
            // batas harga
            [
                'key' => 'batas_harga',
                'label' => 'Batas Harga',
                'type' => 'number',
                'headerClass' => 'w-20'
            ],
        ];

        $ItemView = $items->map(function ($subKategori) {
            return [
                'id' => $subKategori->id,
                'nama_kategori' => $subKategori->kategori->nama_kategori,
                'kategori_id' => $subKategori->kategori_id,
                'nama_sub_kategori' => $subKategori->nama_sub_kategori,
                'batas_harga' => $subKategori->batas_harga,
            ];
        });

        $kategori = Kategori::all();
        // Return the index view with the paginated items
        return Inertia::render('SubKategori/Index', [
            'items' => $items,
            'ItemView' => $ItemView,
            'columns' => $column,
            'filters' => $request->all('search', 'trashed'),
            'perPage' => $perPage,
            'kategori' => $kategori,
            'meta' => [
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // dd($request->all());

        $validatedData = $request->validate([
            'kategori_id' => 'required|exists:kategoris,id',
            'nama_sub_kategori' => 'required|string|max:255',
            'batas_harga' => 'required|numeric|min:0',
        ]);
        // Create a new SubKategori instance
        SubKategori::create($validatedData);
        return redirect()->route('sub-kategori.index')->with('success', 'Sub Kategori created successfully.');
    }

    /**
     * Display 

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(SubKategori $subKategori)
    // {
    //     // Show the edit form for the specified subKategori
    //     return Inertia::render('SubKategori/Edit', [
    //         'subKategori' => $subKategori,
    //         'kategori' => Kategori::all(),
    //     ]);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubKategoriRequest $request, SubKategori $subKategori)
    {
        // Validate the request
        $validatedData = $request->validated();
        // Update the SubKategori instance
        $subKategori->update($validatedData);
        // Redirect back to the index with a success message    
        return redirect()->route('sub-kategori.index')->with('success', 'Sub Kategori updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubKategori $subKategori)
    {
        // Delete the SubKategori instance
        $subKategori->delete();
        // Redirect back to the index with a success message
        return redirect()->route('sub-kategori.index')->with('success', 'Sub Kategori deleted successfully.');
    }
}
