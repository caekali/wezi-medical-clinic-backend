<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

    public function getAllServices()
    {
        $services = Service::with('department')->get();

        $services = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'status' => $service->status,
                'price' => $service->price,
                'department' => $service->department?->name,
            ];
        });

        return response()->json($services);
    }

    public function index($departmentId)
    {
        $services = Service::with('department')
            ->where('department_id', $departmentId)
            ->get();

        $services = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'status' => $service->status,
                'price' => $service->price,
                'department' => $service->department?->name,
            ];
        });

        return response()->json($services);
    }



    public function show($departmentId, $serviceId)
    {
        $service = Service::with('department')->findOrFail($serviceId);

        return response()->json([
            'id' => $service->id,
            'name' => $service->name,
            'department' => $service->department?->name,
        ]);
    }


    public function store(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'status' => 'string'
        ]);

        $service = $department->services()->create($request->only(['name', 'price', 'status']));

        return response()->json($service, 201);
    }


    public function update(Request $request, Department $department, Service $service)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'float',
            'status' => 'required|string'
        ]);

        $service->update([
            'name' => $request->name,
            'price' => $request->price,
            'status' => $request->status
        ]);
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
