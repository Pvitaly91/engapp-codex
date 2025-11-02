@extends('layouts.engram')

@section('title', 'Engram — Вивчення англійської онлайн')

@section('content')
<!-- Hero Section -->
<section class="py-16 md:py-24">
  <div class="max-w-4xl mx-auto text-center">
    <div class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-sm font-medium mb-6">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
      </svg>
      Платформа для ефективного вивчення англійської
    </div>

    <h1 class="text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">
      Вивчайте англійську з Engram
    </h1>

    <p class="text-lg md:text-xl text-muted-foreground mb-10 max-w-2xl mx-auto">
      Покращуйте граматику, розширюйте словниковий запас та практикуйте переклад за допомогою інтерактивних тестів та структурованої теорії
    </p>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
      <a href="{{ route('words.test') }}"
         class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-8 py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transition-all hover:scale-105">
        Почати навчання
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
      </a>
      <a href="{{ route('pages.index') }}"
         class="inline-flex items-center gap-2 border-2 border-border bg-background px-8 py-4 rounded-xl font-semibold text-lg hover:bg-muted transition-all">
        Переглянути теорію
      </a>
    </div>
  </div>
</section>

<!-- Features Grid -->
<section class="py-16 border-y border-border bg-muted/30">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold mb-4">Що ми пропонуємо?</h2>
      <p class="text-muted-foreground text-lg">Комплексний підхід до вивчення англійської мови</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Words Test -->
      <div class="group bg-card border border-border rounded-2xl p-6 hover:shadow-soft transition-all hover:scale-105">
        <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Словниковий запас</h3>
        <p class="text-muted-foreground mb-4 text-sm">Розширюйте словник з випадковими тестами та перекладами</p>
        <a href="{{ route('words.test') }}" class="text-primary hover:underline font-medium inline-flex items-center gap-1">
          Спробувати
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>

      <!-- Grammar Test -->
      <div class="group bg-card border border-border rounded-2xl p-6 hover:shadow-soft transition-all hover:scale-105">
        <div class="w-12 h-12 bg-secondary/10 text-secondary rounded-xl flex items-center justify-center mb-4 group-hover:bg-secondary group-hover:text-secondary-foreground transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Граматичні тести</h3>
        <p class="text-muted-foreground mb-4 text-sm">Створюйте персоналізовані тести для різних часів</p>
        <a href="{{ route('grammar-test') }}" class="text-secondary hover:underline font-medium inline-flex items-center gap-1">
          Спробувати
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>

      <!-- Translation Test -->
      <div class="group bg-card border border-border rounded-2xl p-6 hover:shadow-soft transition-all hover:scale-105">
        <div class="w-12 h-12 bg-accent/10 text-accent rounded-xl flex items-center justify-center mb-4 group-hover:bg-accent group-hover:text-accent-foreground transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Переклад речень</h3>
        <p class="text-muted-foreground mb-4 text-sm">Практикуйте переклад з англійської українською</p>
        <a href="{{ route('translate.test') }}" class="text-accent hover:underline font-medium inline-flex items-center gap-1">
          Спробувати
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>

      <!-- Question Review -->
      <div class="group bg-card border border-border rounded-2xl p-6 hover:shadow-soft transition-all hover:scale-105">
        <div class="w-12 h-12 bg-success/10 text-success rounded-xl flex items-center justify-center mb-4 group-hover:bg-success group-hover:text-white transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Перегляд помилок</h3>
        <p class="text-muted-foreground mb-4 text-sm">Аналізуйте та виправляйте складні питання</p>
        <a href="{{ route('question-review.index') }}" class="text-success hover:underline font-medium inline-flex items-center gap-1">
          Спробувати
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Why Choose Engram -->
<section class="py-16">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold mb-4">Чому саме Engram?</h2>
      <p class="text-muted-foreground text-lg">Переваги нашої платформи</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="text-center">
        <div class="w-16 h-16 bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Швидке навчання</h3>
        <p class="text-muted-foreground">Короткі ефективні тести, які не займають багато часу</p>
      </div>

      <div class="text-center">
        <div class="w-16 h-16 bg-gradient-to-br from-secondary to-accent rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Відстеження прогресу</h3>
        <p class="text-muted-foreground">Зберігайте результати та аналізуйте ваш розвиток</p>
      </div>

      <div class="text-center">
        <div class="w-16 h-16 bg-gradient-to-br from-accent to-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Структурована теорія</h3>
        <p class="text-muted-foreground">Зрозумілі пояснення граматичних правил з прикладами</p>
      </div>
    </div>
  </div>
</section>

<!-- Grammar Preview Section -->
<section class="py-16 border-y border-border bg-gradient-to-br from-primary/5 via-secondary/5 to-accent/5">
  <div class="max-w-5xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold mb-4">Вивчайте граматику систематично</h2>
      <p class="text-muted-foreground text-lg">Всі часи англійської мови з прикладами та поясненнями</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-card border border-border rounded-xl p-4 text-center hover:shadow-md transition-shadow">
        <div class="text-2xl font-bold text-primary mb-1">Present</div>
        <div class="text-sm text-muted-foreground">4 часи</div>
      </div>
      <div class="bg-card border border-border rounded-xl p-4 text-center hover:shadow-md transition-shadow">
        <div class="text-2xl font-bold text-secondary mb-1">Past</div>
        <div class="text-sm text-muted-foreground">4 часи</div>
      </div>
      <div class="bg-card border border-border rounded-xl p-4 text-center hover:shadow-md transition-shadow">
        <div class="text-2xl font-bold text-accent mb-1">Future</div>
        <div class="text-sm text-muted-foreground">4 часи</div>
      </div>
      <div class="bg-card border border-border rounded-xl p-4 text-center hover:shadow-md transition-shadow">
        <div class="text-2xl font-bold text-success mb-1">Conditionals</div>
        <div class="text-sm text-muted-foreground">+ Інше</div>
      </div>
    </div>

    <div class="text-center mt-8">
      <a href="{{ route('pages.index') }}"
         class="inline-flex items-center gap-2 bg-card border-2 border-primary text-primary px-6 py-3 rounded-xl font-semibold hover:bg-primary hover:text-primary-foreground transition-all">
        Переглянути всю теорію
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
      </a>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="py-20">
  <div class="max-w-4xl mx-auto text-center">
    <div class="bg-gradient-to-br from-primary via-secondary to-accent p-1 rounded-3xl">
      <div class="bg-background rounded-3xl p-8 md:p-12">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Готові почати?</h2>
        <p class="text-lg text-muted-foreground mb-8">
          Приєднуйтесь до тисяч користувачів, які вже покращують свою англійську з Engram
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <a href="{{ route('words.test') }}"
             class="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-secondary text-white px-8 py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transition-all hover:scale-105">
            Почати тестування
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </a>
          <a href="{{ route('saved-tests.cards') }}"
             class="inline-flex items-center gap-2 border-2 border-border bg-background px-8 py-4 rounded-xl font-semibold text-lg hover:bg-muted transition-all">
            Переглянути збережені тести
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats Section (Optional) -->
<section class="py-12 border-t border-border">
  <div class="max-w-4xl mx-auto">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
      <div>
        <div class="text-3xl md:text-4xl font-bold text-primary mb-2">1000+</div>
        <div class="text-sm text-muted-foreground">Слів для вивчення</div>
      </div>
      <div>
        <div class="text-3xl md:text-4xl font-bold text-secondary mb-2">12</div>
        <div class="text-sm text-muted-foreground">Граматичних часів</div>
      </div>
      <div>
        <div class="text-3xl md:text-4xl font-bold text-accent mb-2">500+</div>
        <div class="text-sm text-muted-foreground">Речень для перекладу</div>
      </div>
      <div>
        <div class="text-3xl md:text-4xl font-bold text-success mb-2">24/7</div>
        <div class="text-sm text-muted-foreground">Доступ до матеріалів</div>
      </div>
    </div>
  </div>
</section>
@endsection
