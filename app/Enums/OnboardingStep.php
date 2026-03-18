<?php

namespace App\Enums;

enum OnboardingStep: string
{
    case Profile = 'profile';
    case Skills = 'skills';
    case Experience = 'experience';
    case Education = 'education';
    case Preferences = 'preferences';
    case Assessment = 'assessment';
    case Review = 'review';

    public function order(): int
    {
        return match ($this) {
            self::Profile => 1,
            self::Skills => 2,
            self::Experience => 3,
            self::Education => 4,
            self::Preferences => 5,
            self::Assessment => 6,
            self::Review => 7,
        };
    }

    public function previous(): ?self
    {
        $currentOrder = $this->order();
        if ($currentOrder <= 1) {
            return null;
        }

        return self::fromOrder($currentOrder - 1);
    }

    public function next(): ?self
    {
        $currentOrder = $this->order();
        if ($currentOrder >= 7) {
            return null;
        }

        return self::fromOrder($currentOrder + 1);
    }

    public static function fromOrder(int $order): self
    {
        return match ($order) {
            1 => self::Profile,
            2 => self::Skills,
            3 => self::Experience,
            4 => self::Education,
            5 => self::Preferences,
            6 => self::Assessment,
            7 => self::Review,
        };
    }

    public static function initial(): self
    {
        return self::Profile;
    }
}
