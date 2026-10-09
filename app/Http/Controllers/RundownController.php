<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\RundownEvent;
use Illuminate\Http\Request;

class RundownController extends Controller
{
    public function index()
    {
        $wedding = Wedding::current();
        $rundowns = $wedding->rundownEvents()->orderBy('id')->get()
            ->sort(function (RundownEvent $first, RundownEvent $second) {
                $firstTime = $this->startTimeInMinutes($first->time);
                $secondTime = $this->startTimeInMinutes($second->time);

                return [$firstTime, $first->id] <=> [$secondTime, $second->id];
            })
            ->values();

        return view('rundowns.index', compact('wedding', 'rundowns'));
    }

    public function store(Request $request)
    {
        $wedding = Wedding::current();
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'activity' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['time'] = $validated['start_time'].' - '.$validated['end_time'];
        unset($validated['start_time'], $validated['end_time']);

        $wedding->rundownEvents()->create($validated);

        return redirect()->route('rundowns.index')->with('success', 'Jadwal acara berhasil ditambahkan!');
    }

    public function update(Request $request, RundownEvent $rundown)
    {
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'activity' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['time'] = $validated['start_time'].' - '.$validated['end_time'];
        unset($validated['start_time'], $validated['end_time']);

        $rundown->update($validated);

        return redirect()->route('rundowns.index')->with('success', 'Jadwal acara diperbarui!');
    }

    private function startTimeInMinutes(string $timeRange): int
    {
        if (! preg_match('/^\s*(\d{1,2})[:.](\d{2})/', $timeRange, $matches)) {
            return PHP_INT_MAX;
        }

        $hours = (int) $matches[1];
        $minutes = (int) $matches[2];

        if ($hours > 23 || $minutes > 59) {
            return PHP_INT_MAX;
        }

        return ($hours * 60) + $minutes;
    }

    public function destroy(RundownEvent $rundown)
    {
        $rundown->delete();
        return redirect()->route('rundowns.index')->with('success', 'Jadwal acara dihapus!');
    }
}
