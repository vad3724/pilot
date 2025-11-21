<?php
declare(strict_types=1);

function pilotNewYear(int $targetYear): DateTime
{
    $start = new DateTime('2020-01-01 12:00:00', new DateTimeZone('Europe/Moscow'));

    $k = $targetYear - 2021;
    if ($k < 0) {
        return $start;
    }

    $cycle = intdiv($k, 3);
    $position = $k % 3;

    $baseHours = [15, 18, 96];
    $hours = $baseHours[$position] + $cycle * 192;

    $start->modify("+$hours hours");
    return $start;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = filter_input(
        INPUT_POST,
        'year',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 9999]]
    );

    header('Content-Type: application/json; charset=UTF-8');

    if ($year === false || $year === null) {
        echo json_encode([
            'ok' => false,
            'error' => 'Введите корректный год (целое число).',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = pilotNewYear($year);

    echo json_encode([
        'ok' => true,
        'year' => $year,
        'datetime' => $result->format('Y-m-d H:i:s'),
        'display_time' => $result->format('H:i:s'),
        'timezone' => 'Europe/Moscow',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$currentTime = (new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')))->format('d.m.Y H:i:s');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Pilot New Year</title>
    <style>
        :root { color-scheme: light dark; }
        body { font-family: system-ui, sans-serif; margin: 3rem; background: #f6f8fa; color: #0f1419; }
        main { padding: 2rem; border-radius: 12px; background: #fff; box-shadow: 0 15px 35px rgba(15, 20, 25, 0.08); max-width: 640px; }
        h1 { margin-top: 0; }
        form { display: flex; gap: 0.5rem; align-items: flex-end; flex-wrap: wrap; margin-bottom: 1rem; }
        label span { display:block;font-size:0.85rem;color:#475569;margin-bottom:0.25rem; }
        input[type="number"] { padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid #cfd8e3; min-width: 180px; font-size: 1rem; }
        button { padding: 0.6rem 1.1rem; border: none; border-radius: 8px; background: #2563eb; color: #fff; cursor: pointer; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        #history { margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
        .entry { padding: 0.65rem 0.9rem; border-radius: 10px; background: #f1f5f9; border: 1px solid #d7e3f4; }
        .entry strong { display: inline-block; min-width: 4rem; }
        .meta { color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .error { color: #dc2626; margin-top: 0.5rem; min-height: 1.2rem; }
    </style>
</head>
<body>
<main>
    <h1>Пилотируемый Новый год</h1>

    <form id="year-form" autocomplete="off">
        <label>
            <span>Введите год</span>
            <input type="number" name="year" min="1" max="9999" required placeholder="например, 2025">
        </label>
        <button type="submit">Посчитать</button>
    </form>
    <p id="error" class="error" role="alert" aria-live="polite"></p>

    <section>
        <h2 style="margin-bottom:0.5rem;">История запросов</h2>
        <div id="history" aria-live="polite"></div>
    </section>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-3fp9R8bRv9/6Z8SGvtQvVCOH14R/2uOeGjn5ERu0Gyk="
        crossorigin="anonymous"></script>
<script>
$(function () {
    const $form = $('#year-form');
    const $input = $form.find('input[name="year"]');
    const $history = $('#history');
    const $error = $('#error');

    $form.on('submit', function (event) {
        event.preventDefault();
        $error.text('');

        const year = $.trim($input.val());
        if (!year) {
            $error.text('Пожалуйста, введите год.');
            return;
        }

        $.ajax({
            url: '',
            method: 'POST',
            dataType: 'json',
            data: { year }
        })
            .done(function (payload) {
                if (!payload.ok) {
                    $error.text(payload.error || 'Не удалось выполнить расчёт.');
                    return;
                }

                $input.val('');

                $('<div>')
                    .addClass('entry')
                    .html(`<strong>Год ${payload.year}</strong> → ${payload.display_time} по Москве`)
                    .prependTo($history);
            })
            .fail(function () {
                $error.text('Ошибка сети. Попробуйте ещё раз.');
            });
    });
});
</script>
</body>
</html>

