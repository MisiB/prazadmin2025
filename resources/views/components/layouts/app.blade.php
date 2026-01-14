<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="lofi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    {{-- Signature Pad  --}}
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-white overflow-x-hidden">
 
    
    {{-- MAIN --}}
    <x-main full-width>       
        <livewire:components.sidebar/>       
      
        <x-slot:content>           
            <livewire:components.topbar />
            
            {{ $slot }}
            
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
    <script async
    src="https://g43kpaqcf35c36jviqbbeqwi.agents.do-ai.run/static/chatbot/widget.js"
    data-agent-id="8f8a0e67-aeb0-11f0-b074-4e013e2ddde4"
    data-chatbot-id="5uSasRSMtpSzuoNvMvhs-V_PzdHjerK4"
    data-name="PRAZ  Chatbot"
    data-primary-color="#0c7d1b"
    data-secondary-color="#E5E8ED"
    data-button-background-color="#0c7d1b"
    data-starting-message="Hello! How can I help you today?"
    data-logo="/static/chatbot/icons/default-agent.svg">
   </script>
</body>
</html>
