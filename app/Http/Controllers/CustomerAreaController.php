<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerArea;

class CustomerAreaController extends Controller
{
 public function store(Request $request)
{
    $request->validate([
        'areaCode'  => 'required|string|max:50|unique:customerareas,areaCode',
        'areaName'  => 'required|string|max:100',
        'ajentName' => 'required|string|max:100',
    ]);

    $area = CustomerArea::create([
        'areaCode'  => $request->areaCode,
        'areaName'  => $request->areaName,
        'ajentName' => $request->ajentName,
    ]);

    return response()->json([
        'success' => true,
        'data'    => $area
    ]);
}



public function getsAreas(Request $request)
{
    $query = $request->input('search');
    $areas = CustomerArea::when($query, function($q) use ($query){
        $q->where('areaCode', 'like', "%{$query}%")
          ->orWhere('areaName', 'like', "%{$query}%");
    })->orderBy('id', 'desc')->get();

    return response()->json($areas);
}


public function destroy($id)
{
    CustomerArea::findOrFail($id)->delete();
    return redirect()->back()->with('success', 'Catagory deleted successfully.');
}


}

