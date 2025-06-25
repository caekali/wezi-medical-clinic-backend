<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
   
    public function index(Department $department)
    {
        $services = $department->services()->get();
        return response()->json($services);
    }

   
    public function store(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $service = $department->services()->create([
            'name' => $request->name
        ]);

        return response()->json($service, 201);
    }

  
    public function update(Request $request, Department $department, Service $service)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $service->update(['name' => $request->name]);
        return response()->json($service);
    }

   
    public function destroy(Department $department, Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Service deleted']);
    }

  
    public function trashed()
    {
        $trashed = Service::onlyTrashed()->get();
        return response()->json($trashed);
    }

   
    public function restore($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->restore();
        return response()->json(['message' => 'Service restored']);
    }
}
