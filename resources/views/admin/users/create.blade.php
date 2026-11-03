@extends('layouts.admin')
@section('title', 'Tambah Petani')
@section('breadcrumb', 'Tambah Petani')
@section('content')
@include('admin.users.form', ['editing' => false, 'farmer' => null])
@endsection
