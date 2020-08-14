@extends('home')

@section('body')
	<div class="card">
    <div class="card-header">Menu</div>

    <div class="card-body"> 
    	<div class="row">			
			<div class="col-sm-12">
				<div class="card">
				  	<div class="card-body">
				    	<h5 class="card-title text-success">Chat en tiempo real</h5>
				    	<p class="card-text">Conectate con tus amigos ahora, cuentales como va tu cuarentena, saludalos para su cumpeaños! No esperes mas!! </p>
				    	<a href="{{route('chat.index')}}" class="btn btn-primary">a chatear!!!</a>
				  </div>
				</div>
			</div>
		</div>
    </div>
</div>
@endsection
