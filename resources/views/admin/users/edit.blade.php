@extends('layouts.admin')
@section('title', 'Edit Akun Petani')
@section('breadcrumb', 'Edit Akun Petani')
@section('content')
@include('admin.users.form', ['editing' => true])
@endsection
