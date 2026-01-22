<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\EmailSender;
use App\Helpers\WhatsappApi;
use App\Http\Controllers\Controller;
use App\Models\BookingContact\BookingContact;
use App\Models\Company\CompanyModel;
use App\Models\Events\Events;
use App\Models\Events\EventsCategory;
use App\Models\Events\EventsCategoryList;
use App\Models\Events\UserRegister;
use App\Models\MemberModel;
use App\Models\Payments\Payment;
use App\Models\Profiles\ProfileModel;
use App\Models\Sponsors\Sponsor;
use App\Models\User;
use App\Services\Events\EventsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Xendit\Invoice;
use Xendit\Xendit;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Support\QrCode;
use Symfony\Component\HttpKernel\Profiler\Profile;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $list = EventsService::showAll();

        $data = [
            'list' => $list
        ];
        return view('admin.events.event', $data);
    }

    public function create()
    {
        $categories = EventsCategory::orderBy('id', 'desc')->get();

        $data = [
            'categories' => $categories
        ];
        return view('admin.events.create', $data);
    }

    public function edit($id)
    {
        $findEvent = EventsService::showDetail($id);
        $data = [
            'data' => $findEvent
        ];
        return view('admin.events.edit', $data);
    }

    public function update(Request $request)
    {
        // dd($request->all());
        $findEvent = EventsService::showDetail($request->id);
        $findEvent->name = $request->name;
        $findEvent->location = $request->location;
        $findEvent->description = $request->description;
        $findEvent->type = $request->type;
        $findEvent->event_type = $request->event_type;
        $findEvent->location = $request->location;
        $findEvent->start_date = $request->start_date;
        $findEvent->end_date = $request->end_date;
        $findEvent->start_time = $request->start_time;
        $findEvent->end_time = $request->end_time;
        $findEvent->status = $request->status;
        $findEvent->slug = Str::slug($request->name);
        $findEvent->link = $request->link;
        $findEvent->status_event = $request->status_event;
        $findEvent->maps = $request->maps;
        $file = $request->image;
        if (!empty($file)) {
            $imageName = time() . '.' . $request->image->extension();
            $db = '/storage/events/' . $imageName;
            $findEvent_folder = $request->image->storeAs('public/events', $imageName);
            $findEvent->image = $db;
        }
        $image_banner = $request->image_banner;
        if (!empty($image_banner)) {
            $imageName2 = time() . '.' . $request->image_banner->extension();
            $db2 = '/storage/events-banner/' . $imageName2;
            $save_folder = $request->image_banner->storeAs('public/events-banner', $imageName2);
            $findEvent->image_banner = $db2;
        }
        $findEvent->save();
        return redirect()->route('events')->with('success', 'Successfully Update event');
    }

    public function store(Request $request)
    {
        $save = new Events();
        $save->name = $request->name;
        $save->location = $request->location;
        $save->description = $request->description;
        $save->type = $request->type;
        $save->start_date = $request->start_date;
        $save->end_date = $request->end_date;
        $save->start_time = $request->start_time;
        $save->end_time = $request->end_time;
        $save->status = $request->status;
        $save->event_type = $request->event_type;
        $save->slug = Str::slug($request->name);
        $save->link = $request->link;
        $save->status_event = $request->status_event;
        $save->maps = $request->maps;
        $file = $request->image;
        if (!empty($file)) {
            $imageName = time() . '.' . $request->image->extension();
            $db = '/storage/events/' . $imageName;
            $save_folder = $request->image->storeAs('public/events', $imageName);
            $save->image = $db;
        }
        $image_banner = $request->image_banner;
        if (!empty($image_banner)) {
            $imageName2 = time() . '.' . $request->image_banner->extension();
            $db2 = '/storage/events-banner/' . $imageName2;
            $save_folder = $request->image->storeAs('public/events-banner', $imageName2);
            $save->image_banner = $db2;
        }
        $save->save();
        if (!empty($request->category)) {
            foreach ($request->category_id as $key => $value) {
                $category = EventsCategoryList::create([
                    'events_id' => $save->id,
                    'events_category_id' => $request->category_id[$key]
                ]);
            }
        }
        return redirect()->route('events')->with('success', 'Successfully create new event');
    }

    public function destroy(Request $request)
    {
        $data = Events::where('id', $request->id)->delete();
        // activity()->log('Menghapus Data Kategori');
        return response()->json(['success' => true]);
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'uploaded_file' => 'required|file|mimes:xls,xlsx'
        ]);
        $the_file = $request->file('uploaded_file');
        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet        = $spreadsheet->getActiveSheet();
            $row_limit    = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range    = range(2, $row_limit);
            $column_range = range('M', $column_limit);
            $startcount = 1;
            $data = array();
            foreach ($row_range as $row) {
                $user = MemberModel::firstOrNew(array('email' => $sheet->getCell('E' . $row)->getValue()));
                $user->company_name = $sheet->getCell('A' . $row)->getValue();
                $user->name = $sheet->getCell('B' . $row)->getValue();
                $user->job_title = $sheet->getCell('C' . $row)->getValue();
                $user->phone = $sheet->getCell('D' . $row)->getValue();
                $user->email = $sheet->getCell('E' . $row)->getValue();
                $user->company_website = $sheet->getCell('F' . $row)->getValue();
                $user->company_category = $sheet->getCell('G' . $row)->getValue();
                $user->company_other = $sheet->getCell('H' . $row)->getValue();
                $user->address = $sheet->getCell('I' . $row)->getValue();
                $user->city = $sheet->getCell('J' . $row)->getValue();
                $user->portal_code = $sheet->getCell('K' . $row)->getValue();
                $user->office_number = $sheet->getCell('L' . $row)->getValue();
                $user->register_as = $sheet->getCell('M' . $row)->getValue();
                $user->save();
                $codePayment = strtoupper(Str::random(7));
                $payment = Payment::firstOrNew(array('member_id' => $user->id));
                $payment->member_id = $user->id;
                $payment->package = 'free';
                $payment->code_payment = $codePayment;
                $payment->price = 0;
                $payment->status = 'Waiting';
                $payment->save();
                $startcount++;
            }
            return back()->with('success', 'Success Import ' . $startcount . ' data');
        } catch (Exception $e) {
            $error_code = $e->errorInfo[1];
            return back()->withErrors('There was a problem uploading the data!');
        }
    }
}
