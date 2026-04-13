<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Customers;
use App\Models\OurCompany;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Schmeits\FilamentPhosphorIcons\Support\Icons\Phosphor;
use ZipStream\Option\Archive;
class TopBar extends Component implements HasSchemas,HasActions
{
    use InteractsWithSchemas,InteractsWithActions;
    public $status;
    public $name;
    public function optionSelected()
    {
        User::find(Auth::id())->update(['company' => $this->status]);
        $this->name=Auth::user()->company;
        $this->render();
        return redirect(request()->header('Referer'));

    }
    public function mount(){
        $this->name=Auth::user()->company;
    }
    public $filename;
    public $comp;
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
               Select::make('name')
                ->columnSpan(4)
                   ->afterStateUpdated(function ($state){
                       User::find(Auth::id())->update(['company' => $state]);
                       $this->name=Auth::user()->company;
                       $this->render();
                       return redirect(request()->header('Referer'));
                   })
                ->live()
                ->hiddenLabel()
                ->options(OurCompany::all()->pluck( 'Company','Company'))
                ->searchable()
                ->preload(),
                Actions::make([
                    Action::make('doBackup')
                        ->label('نسخ احتياطي')
                        ->size(Size::ExtraSmall)
                        ->icon(Phosphor::Database)
                        ->outlined()
                        ->action(function (){
                            if(\File::exists(public_path(Auth()->user()->company.'_'.date('Ymd').'.zip'))){
                                \File::delete(public_path(Auth()->user()->company.'_'.date('Ymd').'.zip'));
                            }

                            $this->comp=Auth()->user()->company;
                            $this->filename=$this->comp.'_'.date('Ymd').'.bak';

                            Storage::delete($this->filename);

                            sqlsrv_configure('WarningsReturnAsErrors',0);

                            $path=storage_path().'\app';

                            $serverName = ".";
                            $connectionInfo = array( "Database"=>"master","TrustServerCertificate"=>"True","UID"=>"hameed",
                                "PWD"=>"Medo_2003", "CharacterSet" => "UTF-8");
                            $conn = sqlsrv_connect( $serverName, $connectionInfo);

                            // $comp=Auth()->user()->company;
                            // $this->filename=$comp.'_'.date('Ymd').'.bak';
                            Storage::put('file.sql', 'declare
    @path varchar(100),
    @fileDate varchar(20),
    @fileName varchar(140)

    SET @path = \''.$path.'\\\'
    SELECT @fileDate = CONVERT(VARCHAR(20), GETDATE(), 112)
    SET @fileName = @path + \''.$this->filename.'\'
    BACKUP DATABASE '.$this->comp.' TO DISK=@fileName');



                            $strSQL = Storage::get('file.sql');

                            //   $strSQL = file_get_contents("c:\backup\arch.sql");
                            if (!empty($strSQL)) {
                                $query = sqlsrv_query($conn, $strSQL);
                                if ($query === false) {
                                    die(var_export(sqlsrv_errors(), true));
                                } else {


                                }
                            }

                            Storage::download($this->filename);


                            $zip_file = Auth()->user()->company.'_'.date('Ymd').'.zip'; // Name of our archive to download
                            $zip = new \ZipArchive();
                            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                            $invoice_file = Auth()->user()->company.'_'.date('Ymd').'.bak';
                            $zip->addFile(storage_path(). "/app/".$invoice_file, $invoice_file);

                            $zip->close();
                            Storage::delete($invoice_file);

                            return response()->download($zip_file)->deleteFileAfterSend(true);
                        }),
                  ]
                )->columnSpan(2)->verticalAlignment(VerticalAlignment::Center)

                 //->outlined()
                 //->iconButton()
            ])->columns(8);
    }
    public function render()
    {
        $company=OurCompany::query()->get();

        return view('livewire.top-bar',['company'=>$company,'name'=>$this->name]);


    }
}
