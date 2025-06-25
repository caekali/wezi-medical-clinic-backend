<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
   
    public function index()
    {
        $departments = Department::all();
        return response()->json($departments);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json($department, 201);
    }

   
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string'
        ]);

        $department->update(['name' => $request->name, 'description' => $request->description]);
        return response()->json($department);
    }

   
    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['message' => 'Department deleted']);
    }

    public function trashed()
    {
        $trashed = Department::onlyTrashed()->get();
        return response()->json($trashed);
    }

    
    public function restore($id)
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();
        return response()->json(['message' => 'Department restored']);
    }
}
