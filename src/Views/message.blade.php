@if (Session::has('message'))
  <div class="alert alert-info">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>
      <i class="fa fa-info-circle fa-lg fa-fw"></i> Info.
    </strong>
    {{ Session::get('message') }}
  </div>
@endif
