<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Checklist;
use App\Models\DropdownOption;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function index(Request $request)
    {
        $wedding = Wedding::current();
        $query = $wedding->checklists();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $checklists = $query->orderBy('deadline', 'asc')->get();

        $categories = DropdownOption::getOptions('checklist_category', ['General', 'Venue', 'Catering', 'Dekorasi', 'Fotografer', 'MUA', 'Undangan', 'Souvenir', 'Acara']);
        $priorities = DropdownOption::getOptions('checklist_priority', ['High', 'Medium', 'Low']);
        $statuses = DropdownOption::getOptions('checklist_status', ['Todo', 'Progress', 'Done']);

        $total = $checklists->count();
        $doneCount = $wedding->checklists()->where('status', 'Done')->count();
        $progressCount = $wedding->checklists()->where('status', 'Progress')->count();
        $todoCount = $wedding->checklists()->where('status', 'Todo')->count();

        return view('checklists.index', compact(
            'wedding',
            'checklists',
            'categories',
            'priorities',
            'statuses',
            'total',
            'doneCount',
            'progressCount',
            'todoCount'
        ));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'deadline' => 'nullable|date',
            'priority' => 'required|string',
            'status' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $wedding->checklists()->create($validated);

        return redirect()->route('checklists.index')->with('success', 'Checklist berhasil ditambahkan!');
    }

    public function update(Request $request, Checklist $checklist)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'deadline' => 'nullable|date',
            'priority' => 'required|string',
            'status' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $checklist->update($validated);

        return redirect()->route('checklists.index')->with('success', 'Checklist berhasil diperbarui!');
    }

    public function toggleStatus(Checklist $checklist)
    {
        $checklist->status = $checklist->status === 'Done' ? 'Todo' : 'Done';
        $checklist->save();

        return redirect()->back()->with('success', 'Status checklist diperbarui!');
    }

    public function duplicate(Checklist $checklist)
    {
        $newChecklist = $checklist->replicate();
        $newChecklist->title = $checklist->title . ' (Copy)';
        $newChecklist->status = 'Todo';
        $newChecklist->save();

        return redirect()->route('checklists.index')->with('success', 'Checklist berhasil diduplikasi!');
    }

    public function destroy(Checklist $checklist)
    {
        $checklist->delete();
        return redirect()->route('checklists.index')->with('success', 'Checklist berhasil dihapus!');
    }
}
