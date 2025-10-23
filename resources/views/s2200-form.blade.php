@extends('layouts.app')

@section('title', 's2200')

@section('main')
    <div class="max-w-100 flex flex-col gap-4 m-auto">
        <h1>s2200</h1>
        <form
            class="flex flex-col gap-6"
            method="POST"
            action="{{ route('s2200.generate-query') }}"
            enctype="multipart/form-data"
        >
            @csrf
            <div class="flex flex-col gap-2">
                <x-text-input
                    name="cpfs"
                    id="cpfs-input"
                    label="CPF's para filtrar os XML's"
                    placeholder="CPF's separados por virgula Ex: 11122233300,11122233300..."
                />
                @error('cpfs')
                    <x-error>{{ $message }}</x-error>         
                @enderror
            </div>
            <div class="flex flex-col gap-2">    
                <x-text-input
                    name="cnpj"
                    id="cnpj-input"
                    label="CNPJ*"
                    placeholder="CNPJ"
                />
                @error('cnpj')
                    <x-error>{{ $message }}</x-error>         
                @enderror
            </div>
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
    </div>