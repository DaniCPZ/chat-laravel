// Funcion que crea el item del usuario conectado
function createItemListUser(user){	
	/*
		-Crea el elemento li 
		-Indica su atributo id con el id del usuario
		-Al evento onclick le carga la funcion load room 
		definida mas abajo con los parametros user.id y user.name
		-Le agrega clases, forma el html de etiqueda li y se lo agrega
		-Retorna el elemento li pero con contenido
	*/
	let element = document.createElement('li');
	element.setAttribute('id',user.id);
	element.setAttribute('onclick', 'loadRoom("' + user.id + '","' + user.name + '")');
	element.classList.add('list-group-item','b-inherit');
	let html = `
		<div class="row">
			<div class="col-md-10">
		  		<h5 class="d-block" style="color:#464646;">${user.name}</h5>
				<span id="preview-${user.id}" class="d-inline-block text-truncate" style="max-width: 180px;">
				</span>
			</div>
			<div class="col-md-2 align-items-center">
				<div class="d-flex align-items-center" style="height: 100%;">
					<span id="n-${user.id}" class="badge badge-primary badge-pill"></span>
				</div>							
			</div>
		</div>`;
	element.innerHTML = html;
	return element;
}

//Funcion que carga sala de chat
function loadRoom(idChat, name)
{	
	let chatActual = sessionStorage.getItem("chat");	
	if(chatActual != idChat){
		/*
			Selecciono la cabecera y el nuevo chat
		*/
		let headerElement = document.getElementById('header-chat');			
		let element = document.getElementById(idChat);	

		/*
			Limpio la vista previa, las notificaciones
			y los items de la lista seleccionados actualmente(selected-item)
		*/
		let notification = document.getElementById('n-' + idChat);
		let preview = document.getElementById('preview-' + idChat);
		let list = document.getElementsByClassName('selected-item');

		preview.innerText = "";
		notification.innerText = "";
		for (let item of list) {
		    item.classList.remove('selected-item');
			item.classList.add('b-inherit');
		}

		/*
			Establezo la clase selected-item al item de la lista users
			y cambio el nombre de la cabecera del chat
		*/
		element.classList.add('selected-item');
		headerElement.innerText = name;
		
		/*
			Indico un nuevo chat abierto en la session
		*/
		sessionStorage.removeItem("chat");
	    sessionStorage.setItem("chat", idChat);	  

	    //Limpia la ventana de chat
	    messagesElement.innerHTML = "";
	    /*
			Envia una peticion que devulve un array con objetos mensaje
			Luego pasa los parametros necesarios para la funcion show room
	    */
	    window.axios.post('/room/'+ idChat)
	    .then(response => {	    	
		    historyMsg = response.data;
		    showRoom(historyMsg,idChat,-20);
		});
	    
	}
}


// A partir del historial del chat carga la vista
function showRoom(historial,idChat, until){
	//Ultimos 20 mensajes por el parametro -20
	chat = historial.slice(until);
	let headerElement = document.getElementById('header-chat');	
	let msg;
	/*
		Recorre el array chat y por cada elemento 
		mensaje( de la base de datos, con id y otros campos innecesarios)
		crea un nuevo objeto con los campos de contenido, nombre y fecha
		y segun sea enviado por mi o no le establezco la clase
		- Envio los parametros a la clase showMessage 
		que me arma el msg para la vista
	*/
	chat.forEach((element) => {
		if(element.user_id == idChat){
			message = {
				content: element.content,
				name: headerElement.innerText,
				fecha: new Date(element.created_at)
			}
			msg = showMessage(message, 'message-received', true);
		}else{
			message = {
				content: element.content,
				name: "Tu",
				fecha: new Date(element.created_at)
			}
			msg = showMessage(message, 'message-sent', true);
		}
		messagesElement.appendChild(msg);
	});
	messageElement.value = "";

	messagesElement.scrollTop = messagesElement.scrollHeight - messagesElement.clientHeight;
}

// Funcion que arma los mensajes
function showMessage(message, className, esChat = false){
	//Variable para el  formateo de la fecha
	let options = {weekday: "long", year: "numeric", month: "long", day: "numeric", hour: "numeric", hour12:"false"};
	let element = document.createElement('div');
	let html;
	/*
		La variable esChat indica si la funcion
		es llamada desde la carga del historial de mensajes o no
	*/
	if(!esChat){

		if(message['user'] && message['content']){
			/*
				Este caso es para cuando un usuario envia un mensaje
				al momento se debe cargarse en la vista 
			*/
			let fecha = new Date();
			fecha.getDate();
			html = `
				<div class="d-flex bd-highlight">
				  	<div class=" flex-grow-1 bd-highlight text-warning font-weight-bold">
				  	${message.user.name}:
				  	</div>
				  	<div class=" bd-highlight" style="color: #bfc4ca;">${fecha.toDateString("es-ES", options)}</div>
				</div>
				<p>${message.content}</p>
		    `;
		}else{
			let fecha = new Date();
			fecha.getDate();
			/*
				Este caso es para cuando los mensajes son recibidos 
				por un evento 
			*/
			html = `
				<div class="d-flex bd-highlight">
				  	<div class=" flex-grow-1 bd-highlight text-primary font-weight-bold">
				  	Tu:
				  	</div>
				  	<div class=" bd-highlight" style="color: #bfc4ca;">${fecha.toLocaleDateString("es-ES", options)}</div>
				</div>
				<p >${message}</p>
		    `;
		}
	}else{

		//Carga el historial del chat
		let textColor;
		if(className == 'message-received'){
			textColor = "text-warning";
		}else{
			textColor = "text-primary";
		}
		html = `
			<div class="d-flex bd-highlight">
			  	<div class=" flex-grow-1 bd-highlight ${textColor} font-weight-bold">
			  	${message.name}:
			  	</div>
			  	<div class=" bd-highlight" style="color: #bfc4ca;">${message.fecha.toLocaleString("es-ES", options)}</div>
			</div>
			<p>${message.content}</p>
	    `;
	}	
	//en todos los casos devuelve un div con todos los atributos correspondientes
    element.classList.add(className);
    element.innerHTML = html;
    return element;
}

function reset(){
	sessionStorage.clear();
}
