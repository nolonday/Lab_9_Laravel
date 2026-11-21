
@extends('layouts.app')

@section('title', 'Контакты')

@section('content')

    <section class="page-section">

        <h1>Контакты</h1>

        <p class="section-description">
            На этой странице представлена контактная информация.
        </p>

        <div class="contacts-grid">

            @forelse ($contacts as $contact)

                <div class="contact-card">

                    <h2>{{ $contact['name'] }}</h2>

                    <p>
                        <strong>Должность:</strong>
                        {{ $contact['position'] }}
                    </p>

                    <p>
                        <strong>Email:</strong>
                        <a href="mailto:{{ $contact['email'] }}">
                            {{ $contact['email'] }}
                        </a>
                    </p>

                    <p>
                        <strong>Телефон:</strong>
                        {{ $contact['phone'] }}
                    </p>

                </div>

            @empty

                <p>Контактная информация пока отсутствует.</p>

            @endforelse

        </div>

    </section>

@endsection
