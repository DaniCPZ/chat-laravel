@extends('home')
@push('styles')
 <style type="text/css">
        #users > li {
          cursor:pointer;
        }
        .b-inherit{
        	background: inherit;
        }
        .list-reset{
        	list-style: none;
        }
        .message-received{
			width:55%;
			background: #435f7a;
			float: left;
			margin-left: 15px;
			color: #fff !important;
			padding: 0.5rem !important;
			margin-bottom: 0.5rem !important;
			margin-top: 0.5rem !important;
			border-radius: 0.25rem !important;
			border: 1px solid #dee2e6 !important;
		}
        .message-sent{
			width:55%;
			float: right;
			margin-right: 15px;
			padding: 0.5rem !important;
			margin-bottom: 0.5rem !important;
			margin-top: 0.5rem !important;
			border-radius: 0.25rem !important;
			border: 1px solid #dee2e6 !important;
        }
        .border-green {
        	border: 1px solid #28a745!important;
        }
        .list-custom li:last-child {
    	border-bottom-width: 1px !important; 
		}
		.selected-item {
		    color: #1d643b;
		    background-color: #d7f3e3;
		    border-color: #c7eed8;
		}
    </style>
@endpush
@section('body')
	<div class="row border">
		<div id="list-user" class="col-md-4 border-right" style="background: #f8f8f8;">
			<div class="row ">
				<div class="col-md-12 border-bottom">
					<div class="d-flex align-items-center">
						<div class="col-md-8 p-2">
							<span class="text-primary font-weight-bold" style="font-size: 20px">Usuarios conectados</span>	
						</div>
						
					</div> 
				</div>
			</div>

			<div class="row" style="height: 550px;overflow-y: scroll;">
				<div class="col-md-12 p-0">
					<ul id="users" class="list-group list-group-flush list-custom" >
					  	
					</ul>
				</div>
			</div>
		</div>
		<div class="col-md-8 pt-1">
			<div class="border-green my-1">
				<span id="header-chat" class="d-block p-2 bg-success text-white">Seleccione un chat</span>
				<div id="messages" style="height: 550px;overflow-y: scroll;">	
				</div>
			</div>
			<div class="input-group my-3 px-2">
			  	<input id="message" type="text" class="form-control" placeholder="Recipient's username" aria-label="Recipient's username" aria-describedby="button-addon2">
			  	<div class="input-group-append">
			    	<button id="send" class="btn btn-outline-secondary" type="button" id="button-addon2">Send</button>
			  	</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
<script>
	reset();
	//Lista de usuarios conectados o  lista users
	const usersElement = document.getElementById('users');

	//Chat
	const messagesElement = document.getElementById('messages');

	//Boton de enviar mensaje
	const sendElement = document.getElementById('send');

	// Input de mensaje
	const messageElement = document.getElementById('message');	

	var historyMsg = [];

	/*
		ESCUCHO EL CANAL CHAT
		-Canal de presencia
		-here: lista de usaurios actuales
		-joining: usuario que se conecta
		-leaving: usuario que se desconecta
	*/
	Echo.join('chat')
		.here((users)=>{
			/*
				Por cada usuario crea un elemento li
				con la funcion createItemListUser 
				- mas detalle - public/js/main.js
			*/
			users.forEach((user, index)=>{
				if(user.id != {{Auth::user()->id}} ){
					let element = createItemListUser(user);
					usersElement.appendChild(element);
				}							
			})
		})
		.joining((user) => {
			/*
				Crea un elemento li
				con la funcion createItemListUser 
				- mas detalle - public/js/main.js
			*/
	        let element = createItemListUser(user);
			usersElement.appendChild(element);
	    })
	    .leaving((user) => {
	    	/*
				Busca el elemento con el id del usuario desconectado y 
				lo remueve, ademas si la sala(id del usuario, no id room) en session Storage 
				coincide elimina la cabecera y el contenido de los mensajes.
	    	*/
	        let element = document.getElementById(user.id);
	        element.parentNode.removeChild(element);
	        let e = sessionStorage.getItem("chat");	 
	        if(e == user.id){
	        	sessionStorage.removeItem("chat");
	        	let headerElement = document.getElementById('header-chat');	
	        	headerElement.innerText = "Seleccione un chat";
	        	messagesElement.innerHTML = "";
	        }
	    });
	/*
		Funcion de enviar mensajes boton id=send envia la peticion
		por post a la ruta /chat/message/{id} donde la funcion messagesSent
		del controlador ChatController trabaja con los modelos y dispara el evento 
	*/
	sendElement.addEventListener('click',(e)=>{
		e.preventDefault();
		let id = sessionStorage.getItem("chat");
		//Solo si hay una sala cargada envia la peticion
		if(id != null){			
			window.axios.post('/chat/message/'+ id,{
				message : messageElement.value
			});
			// Muestra en nuestra vista el mensaje enviado
			let element = showMessage(messageElement.value, 'message-sent');   		
		    messagesElement.appendChild(element);
			messageElement.value = "";
			messagesElement.scrollTop = messagesElement.scrollHeight - messagesElement.clientHeight;
		}else{
			alert("Debes seleccionar alguien con quien chatear");
		}
	});

	/*
		Escucho sobre el canal privado el evento MessageSent
	*/
	Echo.private('chat.message.{{ auth()->user()->id }}')
	    .listen('MessageSent', (e) => {
	    	let chat = sessionStorage.getItem("chat");
	    	/*
				Si el mensaje que llega es del usuario
				con el que tengo el chat abierto cargo el mensaje
				si no genero una notificacion y una vista previa de msj
	    	*/
	    	if( chat == e.message['user'].id){
	    		/*
					Creo el elemento del msj con la funcion showMessage
					y lo agrego al elemento menssages
	    		*/
	    		let element = showMessage(e.message, 'message-received');		
	            messagesElement.appendChild(element);
	            messagesElement.scrollTop = messagesElement.scrollHeight - messagesElement.clientHeight;
	    	}else{    
	    		/*
					Cargo la vista previa y la notificacion
	    		*/		
	    		let listItem = document.getElementById('n-' + e.message['user'].id);
	    		let preview = document.getElementById('preview-' + e.message['user'].id);
	    		preview.innerText = e.message['content'];
	    		let cantMsg = parseInt(listItem.innerText);
	    		if(isNaN(cantMsg)){
	    			listItem.innerText = 1
	    		}else{
	    			listItem.innerText = cantMsg + 1;
	    		};
	    	}	        
	    });
</script>
	
@endpush