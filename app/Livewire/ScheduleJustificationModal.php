<?php

namespace App\Livewire;

use App\Models\Schedule;
use App\Models\ScheduleRequest;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Filament\Facades\Filament;
use Livewire\Component;

class ScheduleJustificationModal extends Component
{
    public function render()
    {
        return view('livewire.schedule-justification-modal');
    }

    public $visible = false;
    public $conflictingScheduleId;
    public $justification = '';
    public $id_subject;
    public $turno;

    protected $rules = [
        'justification' => 'required|min:10',
        // regras para outros campos, se houver
    ];

    protected $listeners = ['openJustificationModal'];

    public function openJustificationModal($conflictingScheduleId)
    {
        $this->conflictingScheduleId = $conflictingScheduleId;
        $this->visible = true;
    }

    public function submit()
    {
        $this->validate();

        $teacher = Teacher::where('id_user', Filament::auth()->id())->first();
        $activeYear = SchoolYear::where('active', true)->first();

        $conflictingSchedule = Schedule::find($this->conflictingScheduleId);

        $schedule = Schedule::create([
            'id_room' => $conflictingSchedule->id_room,
            'id_weekday' => $conflictingSchedule->id_weekday,
            'id_timeperiod' => $conflictingSchedule->id_timeperiod,
            'id_teacher' => $teacher?->id,
            'id_subject' => $this->id_subject,
            'shift' => $this->shift,
            'id_schoolyear' => $activeYear?->id,
            'status' => 'Pendente',
        ]);

        ScheduleRequest::create([
            'id_schedule' => $this->conflictingScheduleId,
            'id_teacher' => $teacher?->id,
            'id_new_schedule' => $schedule->id,
            'justification' => $this->justification,
            'created_at' => now(),
            'status' => 'Pendente',
        ]);
        
        $this->visible = false;
        $this->justification = '';
        $this->emit('refreshSchedulesTable'); // se quiseres refrescar lista na UI
    }
}
