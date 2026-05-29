<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::withCount('allItems')->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.menus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:50|unique:menus,location',
            'status' => 'boolean',
        ]);

        $data = $request->only(['name', 'location']);
        $data['status'] = $request->has('status');

        Menu::create($data);

        return redirect()->route('admin.menus.index')->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu)
    {
        $menu->load('allItems');
        $menuItems = $menu->items()->with('children')->get();
        return view('admin.menus.edit', compact('menu', 'menuItems'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:50|unique:menus,location,' . $menu->id,
            'status' => 'boolean',
        ]);

        $data = $request->only(['name', 'location']);
        $data['status'] = $request->has('status');

        $menu->update($data);

        // Update menu items if provided
        if ($request->has('items')) {
            // Remove old items and recreate
            $menu->allItems()->delete();

            foreach ($request->items as $index => $item) {
                if (empty($item['title'])) continue;

                $menuItem = MenuItem::create([
                    'menu_id' => $menu->id,
                    'title' => $item['title'],
                    'url' => $item['url'] ?? '#',
                    'target' => $item['target'] ?? '_self',
                    'icon' => $item['icon'] ?? null,
                    'order' => $item['order'] ?? $index,
                    'status' => isset($item['status']),
                ]);
            }
        }

        return redirect()->route('admin.menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return back()->with('success', 'Menu deleted successfully.');
    }
}
