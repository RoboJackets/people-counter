<!DOCTYPE html>
<html lang="en">
  <head>
    @include('layouts/head')
  </head>

  <body>
    <div class="container" id="app">
      @yield('content')
    </div>

    @include('layouts/footer')
  </body>
  <script src="{{ mix('/js/app.js') }}"></script>
</html>
