<?php
namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Task;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;

class PagesController extends Controller
{
    /**
     * Dashobard view
     * @return mixed
     */
    public function dashboard()
    {
        $today = today();
        $startDate = Carbon::now()->startOfMonth();
        
        $openTasks = Task::whereBetween(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"), [$startDate->format('Y-m-d'), $today->format('Y-m-d')])->where('status_id', 1)->count();

        $todayTasks = Task::where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"), $today->format('Y-m-d'))->count();

        $invoiceGenerated = DB::table('invoices')
            ->leftjoin('invoice_lines', 'invoice_id', '=', 'invoices.id')
            ->where(DB::raw("(DATE_FORMAT(invoices.created_at,'%Y-%m-%d'))"), $today->format('Y-m-d'))->sum('invoice_lines.price');

        $paymentsAmount = Payment::where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"), $today->format('Y-m-d'))->sum('amount');

        $period = CarbonPeriod::create($startDate, $today);
        $dates = [];
        $invoiceAmounts = [];
        $paymentDatas = [];
        foreach ($period as $date) {
            $dates[] = $date->format('M d, Y');
            $invoice = DB::table('invoices')
                ->leftjoin('invoice_lines', 'invoice_id', '=', 'invoices.id')
                ->where(DB::raw("(DATE_FORMAT(invoices.created_at,'%Y-%m-%d'))"), $date->format('Y-m-d'))
                ->groupBy(DB::raw("(DATE_FORMAT(invoices.created_at,'%Y-%m-%d'))"))->sum('invoice_lines.price');
            $invoiceAmounts[] = $invoice;
            $payment = Payment::where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"), $date->format('Y-m-d'))
            ->groupBy(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"))
            ->sum('amount');
            $paymentDatas[] = $payment;
        }
        if (!auth()->user()->can('absence-view')) {
            $absences = [];
        } else {
            $absences = Absence::with('user')->groupBy('user_id')->where('start_at', '>=', today())->orWhere('end_at', '>', today())->get();
        }
        return view('pages.dashboard')
            ->withUsers(User::with(['department'])->get())
            ->withUserName(User::pluck('name'))
            ->withTotalTasks($openTasks)
            ->withTodayTasks($todayTasks)
            ->withDates($dates)
            ->withInvoiceGenerated($invoiceGenerated)
            ->withPayments($paymentsAmount)
            ->withInvoiceAmounts($invoiceAmounts)
            ->withPaymentDatas($paymentDatas)
            ->withTotalLeads(Lead::count())
            ->withTotalProjects(Project::count())
            ->withTotalClients(Client::count())
            ->withSettings(Setting::first())
            ->withAbsencesToday($absences);
    }
}
