<?php

namespace App\Enums;

// Используем String Backed Enum, так как будем хранить строки ('pending', 'completed') в БД.
// Это более читаемо в базе данных, чем числа.
enum OrderStatus: string
{
    case Pending = 'pending';       // Ожидает обработки (начальный статус)
    case Processing = 'processing'; // В обработке (принят кухней/менеджером)
    case Shipped = 'shipped';       // Отправлен курьером (если у вас есть доставка)
    case Completed = 'completed';   // Успешно выполнен (доставлен/получен)
    case Cancelled = 'cancelled';   // Отменен (пользователем или менеджером)
    case Failed = 'failed';         // Ошибка (например, проблема с оплатой)


    public function label(): string
    {
        return match($this) {
            self::Pending => 'Ожидает обработки',
            self::Processing => 'В обработке',
            self::Shipped => 'Отправлен',
            self::Completed => 'Выполнен',
            self::Cancelled => 'Отменен',
            self::Failed => 'Ошибка',
        };
    }


    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::Failed]);
    }


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
