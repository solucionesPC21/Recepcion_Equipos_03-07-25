<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use PDF;

class TicketPagoController extends Controller
{
    public function pdf($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            // Manejo del caso cuando el ticket no existe
        }

        // Obtener los conceptos asociados al ticket
        $conceptos = $ticket->concepto;
        
        $pdf = PDF::loadView('pagos.ticket', ['ticket' => $ticket, 'conceptos' => $conceptos])->setPaper(array(0,0,360,792.00), 'portrait');
       // return view('completados.pdfTicket', ['ticket' => $ticket, 'conceptos' => $conceptos]);
        return $pdf->stream();
    }
}
