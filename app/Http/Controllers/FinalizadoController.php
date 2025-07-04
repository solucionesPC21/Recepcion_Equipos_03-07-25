<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TipoEquipo;
use App\Models\Concepto;
use App\Models\Recibo;
use PDF;

class FinalizadoController extends Controller
{
    public function index()
    {
        $recibos = Recibo::where('recibos.id_estado', 3)
            ->leftJoin('tickets', 'recibos.id', '=', 'tickets.id_recibo')
            ->where(function ($query) {
                $query->where('tickets.estado_id', 3)
                    ->orWhereNull('tickets.id');
            })
            ->orderBy('recibos.id', 'DESC')
            ->select('recibos.*', 'tickets.id as ticket_id')
            ->paginate(5);

        $totalRecibos = Recibo::where('recibos.id_estado', 3)
            ->leftJoin('tickets', 'recibos.id', '=', 'tickets.id_recibo')
            ->where(function ($query) {
                $query->where('tickets.estado_id', 3)
                    ->orWhereNull('tickets.id');
            })
            ->count();

        return view('completados.completados', compact('recibos', 'totalRecibos'));
    }


    public function pdf($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            // Manejo del caso cuando el ticket no existe
        }

        // Obtener los conceptos asociados al ticket
        $conceptos = $ticket->concepto;
        
        $pdf = PDF::loadView('completados.pdfTicket', ['ticket' => $ticket, 'conceptos' => $conceptos])->setPaper(array(0,0,360,792.00), 'portrait');
       // return view('completados.pdfTicket', ['ticket' => $ticket, 'conceptos' => $conceptos]);
        return $pdf->stream();
    }

}
