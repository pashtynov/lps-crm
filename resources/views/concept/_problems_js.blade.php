{{-- Данные и скрипт поля «Проблема» — один раз на страницу --}}
@once
    <script>window.LPS_PROBLEMS = @js(\App\Support\ConceptData::problemIndex() + ['url' => route('concept.flat', ['__H__', '__N__'])]);</script>
    <script src="{{ asset('js/concept-problems.js') }}?v=1"></script>
@endonce
