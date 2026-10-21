@extends('errors.base')
@section('code', '404')
@section('heading', 'Not found page 🧭')
@section('message', 'The page you are looking for has been deleted and wrong address. Search, otherwise go to home.')
@section('extra')
  <form action="{{ route('search') }}" method="GET" class="err-search">
    <i class="bi bi-search"></i>
    <input type="search" name="q" class="form-control" placeholder="Search posts...">
  </form>
@endsection
