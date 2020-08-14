<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Message;
use App\Room;
use App\User;
use App\User_chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
  /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('chat.index');
    }

    public function messagesSent(Request $request, $id){
    	$rules = [
    		'message' => 'required'
    	];      
    	$request->validate($rules);
      /*
        Solo si se valida la peticion(request)
        el codigo que sigue se ejecuta
      */

      // Usuario de destino
      $user = User::find($id);      
      $room_id = 0;
      // Usuario que envia el mensaje
      $user2 = $request->user();

      /* 
        Busqueda de la sala en comun de los usuarios
        Por medio de la tabla pivot User_Chat
      */
      foreach($user->rooms as $room) {
          foreach($user2->rooms as $r) {
            if($r->pivot->room_id == $room->pivot->room_id){
                $room_id = $room->pivot->room_id; 
            }
          } 
      } 
      
      /*
        Instancio modelo Message, completo sus propiedades 
        Y Guardo con el metodo ->save()
      */ 
      $msg = new Message;
      $msg->user_id = $user2->id;
      $msg->room_id = $room_id;
      $msg->content = $request->message;
      $msg->save();

      /*
        Armo un array message con 
        el contenido del mensaje(content) ,
        la sala (room) y el usuario emisor(user)
      */
      $message = [
          'content' => $request->message,
          'room' => $room_id,
          'user' => $request->user(),
      ];

      /* 
        Emito el evento app/Events/MessageSent
        que requiere dos parametros        
      */
      broadcast(new MessageSent($user, $message));

      //Devuelvo una con el mensaje enviado
    	return  response()->json($msg);
    }  

    /*
      Trae toda la conversacion de una sala
      a partir de dos usuarios
    */
    function loadRoom(Request $request, $id){
      $user = User::find($id);
      $user2 = $request->user();      
      $room_id = 0;
      foreach($user->rooms as $room) {
          foreach($user2->rooms as $r) {
            if($r->pivot->room_id == $room->pivot->room_id){
                $room_id = $room->pivot->room_id; 
            } 
          }        
      } 
      $room = Room::find($room_id);
      return  response()->json($room->messages);
    }

}
