<?php

namespace RA;

/**
 * Reference: https://github.com/RetroAchievements/RAInterface/blob/master/RA_Emulators.h
 */
abstract class Emulators
{
    // RA Standalone

    public const RAGens = 0;

    public const RAP64 = 1;

    public const RASnes9x = 2;

    public const RAVBA = 3;

    public const RANester = 4; // unused

    public const RANes = 5;

    public const RAPCE = 6;

    public const RALibretro = 7;

    public const RAMeka = 8;

    public const RAQUASI88 = 9;

    public const RAppleWin = 10;

    public const RAOricutron = 11;

    public const RAMelonDS = 12;

    // Third Party Standalone

    public const RetroArch = 'retroarch';

    public const PCSX2 = 'pcsx2';

    public static function cases(): array
    {
        return [
            self::RAGens,
            self::RAP64,
            self::RASnes9x,
            self::RAVBA,
            self::RANester,
            self::RANes,
            self::RAPCE,
            self::RALibretro,
            self::RAMeka,
            self::RAQUASI88,
            self::RAppleWin,
            self::RAOricutron,
            self::RAMelonDS,
        ];
    }
}
