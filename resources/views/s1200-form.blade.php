@extends('layouts.app')

@section('title', 's1200')

@section('main')
    <div class="max-w-100 flex flex-col gap-4 m-auto">
        <h1>s1200</h1>

        <div class="text-green-600">
            {{ session()->get('message') }}
        </div>

        <form
            class="flex flex-col gap-6"
            method="POST"
            action="{{ route('s1200.generate-query') }}"
            enctype="multipart/form-data"
        >
            <div class="flex flex-col gap-2">
                <x-file-input
                    name="xmls[]"
                    id="xmls-input"
                    label="Selecione os xmls do s2200"
                />
                @error('xmls')
                    <x-error>{{ $message }}</x-error>         
                @enderror
            </div>
            <x-button>Gerar query</x-button>
        </form>

        <div>
            <a 
                href="{{ route('s2200.form') }}"
                class="text-blue-600 hover:underline hover:text-blue-800"    
            >Gerar queries do s2200</a>
        </div>
    </div>
@endsection