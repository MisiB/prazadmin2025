<?php

namespace App\Livewire\Components\Workshoporder;

use Livewire\Component;

class Delegates extends Component
{
    public $workshop;
    public function mount($workshop)
    {
        $this->workshop = $workshop;
    }
    public function getdelegates()
    {
        return $this->workshop->delegates;
    }
    public function getheaders():array{
        return [
            ['key'=>'company','label'=>'Company'],
            ['key'=>'name','label'=>'Name'],
            ['key'=>'national_id','label'=>'Nat ID'],
            ['key'=>'title','label'=>'Title'],
            ['key'=>'gender','label'=>'Gender'],
            ['key'=>'type','label'=>'Type'],
            ['key'=>'actions','label'=>''],
        ];
    }
    public function exportdelegates()
    {
        $csvFileName = "Delegates.csv";
        $file = fopen($csvFileName, "w");
        $delegates = $this->getdelegates();
        $array[] = ["Company" => "Company", "Name" => "Name", "Surname" => "Surname", "Email" => "Email", "Phone" => "Phone", "Designation" => "Designation", "National ID" => "National ID", "Title" => "Title", "Gender" => "Gender", "Type" => "Type"];
        foreach ($delegates as $key => $delegate) {
            $array[] = [
                "Company" => $delegate->company,
                "Name" => $delegate->name,
                "Surname" => $delegate->surname,
                "Email" => $delegate->email,
                "Phone" => $delegate->phone,
                "Designation" => $delegate->designation,
                "National ID" => $delegate->national_id,
                "Title" => $delegate->title,
                "Gender" => $delegate->gender,
                "Type" => $delegate->type,
            ];
        }

        foreach ($array as $task) {
            fputcsv($file, $task);
        }

        fclose($file);
        return response()->download(public_path($csvFileName))->deleteFileAfterSend(true);
    }
    public function render()
    {
        return view('livewire.components.workshoporder.delegates',[
            'delegates' => $this->getdelegates(),
            'headers' => $this->getheaders()
        ]);
    }
}
