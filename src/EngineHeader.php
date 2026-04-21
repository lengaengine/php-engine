<?php

declare(strict_types=1);

/**
 * Engine header file. This file contains declarations for the engine's core functionalities
 * and is included in various parts of the engine to provide access to essential features.
 */

// The embed runtime registers the real native functions before project bootstrap.
// These PHP fallbacks are only needed outside the embed SAPI.
if (PHP_SAPI === 'embed') {
    return;
}

use Lenga\Engine\Core\Debug;
use Lenga\Engine\Core\Vector2;
use Lenga\Engine\Enumerations\ForceMode2D;

/** --- Time Functions --- */
if (!function_exists("lenga_internal_time_get_time")) {
    function lenga_internal_time_get_time(): float
    {
        Debug::error("[lenga_internal_time_get_time] Function not implemented.");
        return 0.0;
    }
}

if (!function_exists("lenga_internal_time_get_unscaled_time")) {
    function lenga_internal_time_get_unscaled_time(): float
    {
        Debug::error("[lenga_internal_time_get_unscaled_time] Function not implemented.");
        return 0.0;
    }
}

if (!function_exists("lenga_internal_time_get_delta_time")) {
    function lenga_internal_time_get_delta_time(): float
    {
        Debug::error("[lenga_internal_time_get_delta_time] Function not implemented.");
        return 0.0;
    }
}

if (!function_exists("lenga_internal_time_get_unscaled_delta_time")) {
    function lenga_internal_time_get_unscaled_delta_time(): float
    {
        Debug::error("[lenga_internal_time_get_unscaled_delta_time] Function not implemented.");
        return 0.0;
    }
}

if (!function_exists("lenga_internal_time_get_fixed_delta_time")) {
    function lenga_internal_time_get_fixed_delta_time(): float
    {
        Debug::error("[lenga_internal_time_get_fixed_delta_time] Function not implemented.");
        return 0.0;
    }
}

if (!function_exists("lenga_internal_time_get_fixed_step_count")) {
    function lenga_internal_time_get_fixed_step_count(): int
    {
        Debug::error("[lenga_internal_time_get_fixed_step_count] Function not implemented.");
        return 0;
    }
}

if (!function_exists("lenga_internal_time_get_time_scale")) {
    function lenga_internal_time_get_time_scale(): float
    {
        Debug::error("[lenga_internal_time_get_time_scale] Function not implemented.");
        return 1.0;
    }
}

if (!function_exists("lenga_internal_time_get_effective_time_scale")) {
    function lenga_internal_time_get_effective_time_scale(): float
    {
        Debug::error("[lenga_internal_time_get_effective_time_scale] Function not implemented.");
        return 1.0;
    }
}

if (!function_exists("lenga_internal_time_set_time_scale")) {
    function lenga_internal_time_set_time_scale(float $scale): bool
    {
        Debug::error("[lenga_internal_time_set_time_scale] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_time_set_gameplay_paused")) {
    function lenga_internal_time_set_gameplay_paused(bool $paused): bool
    {
        Debug::error("[lenga_internal_time_set_gameplay_paused] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_time_toggle_gameplay_paused")) {
    function lenga_internal_time_toggle_gameplay_paused(): bool
    {
        Debug::error("[lenga_internal_time_toggle_gameplay_paused] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_time_is_gameplay_paused")) {
    function lenga_internal_time_is_gameplay_paused(): bool
    {
        Debug::error("[lenga_internal_time_is_gameplay_paused] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_time_is_paused")) {
    function lenga_internal_time_is_paused(): bool
    {
        Debug::error("[lenga_internal_time_is_paused] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_engine_set_paused")) {
    function lenga_internal_engine_set_paused(bool $paused): bool
    {
        Debug::error("[lenga_internal_engine_set_paused] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_engine_toggle_paused")) {
    function lenga_internal_engine_toggle_paused(): bool
    {
        Debug::error("[lenga_internal_engine_toggle_paused] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_engine_is_paused")) {
    function lenga_internal_engine_is_paused(): bool
    {
        Debug::error("[lenga_internal_engine_is_paused] Function not implemented.");
        return false;
    }
}

/** --- Preferences Functions --- */
if (!function_exists("lenga_internal_preferences_get_int")) {
    function lenga_internal_preferences_get_int(string $key, int $defaultValue): int
    {
        Debug::error("[lenga_internal_preferences_get_int] Function not implemented. Key: $key");
        return $defaultValue;
    }
}

if (!function_exists("lenga_internal_preferences_set_int")) {
    function lenga_internal_preferences_set_int(string $key, int $value): bool
    {
        Debug::error("[lenga_internal_preferences_set_int] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_preferences_get_float")) {
    function lenga_internal_preferences_get_float(string $key, float $defaultValue): float
    {
        Debug::error("[lenga_internal_preferences_get_float] Function not implemented. Key: $key");
        return $defaultValue;
    }
}

if (!function_exists("lenga_internal_preferences_set_float")) {
    function lenga_internal_preferences_set_float(string $key, float $value): bool
    {
        Debug::error("[lenga_internal_preferences_set_float] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_preferences_get_string")) {
    function lenga_internal_preferences_get_string(string $key, string $defaultValue): string
    {
        Debug::error("[lenga_internal_preferences_get_string] Function not implemented. Key: $key");
        return $defaultValue;
    }
}

if (!function_exists("lenga_internal_preferences_set_string")) {
    function lenga_internal_preferences_set_string(string $key, string $value): bool
    {
        Debug::error("[lenga_internal_preferences_set_string] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_preferences_get_bool")) {
    function lenga_internal_preferences_get_bool(string $key, bool $defaultValue): bool
    {
        Debug::error("[lenga_internal_preferences_get_bool] Function not implemented. Key: $key");
        return $defaultValue;
    }
}

if (!function_exists("lenga_internal_preferences_set_bool")) {
    function lenga_internal_preferences_set_bool(string $key, bool $value): bool
    {
        Debug::error("[lenga_internal_preferences_set_bool] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_preferences_has_key")) {
    function lenga_internal_preferences_has_key(string $key): bool
    {
        Debug::error("[lenga_internal_preferences_has_key] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_preferences_delete_key")) {
    function lenga_internal_preferences_delete_key(string $key): bool
    {
        Debug::error("[lenga_internal_preferences_delete_key] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_preferences_delete_all")) {
    function lenga_internal_preferences_delete_all(): bool
    {
        Debug::error("[lenga_internal_preferences_delete_all] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_preferences_save")) {
    function lenga_internal_preferences_save(): bool
    {
        Debug::error("[lenga_internal_preferences_save] Function not implemented.");
        return false;
    }
}

/** --- Input Functions --- */
if (!function_exists("lenga_internal_input_get_axis")) {
    function lenga_internal_input_get_axis(string $axis): float
    {
        Debug::error("[lenga_internal_input_get_axis] Function not implemented. Axis: $axis");
        return 0.0;
    }
}

if (!function_exists("lenga_internal_input_get_axis_raw")) {
    function lenga_internal_input_get_axis_raw(string $axis): float
    {
        Debug::error("[lenga_internal_input_get_axis_raw] Function not implemented. Axis: $axis");
        return 0.0;
    }
}

if (!function_exists("lenga_internal_input_get_button")) {
    function lenga_internal_input_get_button(string $axis): bool
    {
        Debug::error("[lenga_internal_input_get_button] Function not implemented. Axis: $axis");
        return false;
    }
}

if (!function_exists("lenga_internal_input_get_button_down")) {
    function lenga_internal_input_get_button_down(string $axis): bool
    {
        Debug::error("[lenga_internal_input_get_button_down] Function not implemented. Axis: $axis");
        return false;
    }
}

if (!function_exists("lenga_internal_input_get_button_up")) {
    function lenga_internal_input_get_button_up(string $axis): bool
    {
        Debug::error("[lenga_internal_input_get_button_up] Function not implemented. Axis: $axis");
        return false;
    }
}

if (!function_exists("lenga_internal_input_get_key")) {
    function lenga_internal_input_get_key(int $key): bool
    {
        Debug::error("[lenga_internal_input_get_key] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_input_get_key_down")) {
    function lenga_internal_input_get_key_down(int $key): bool
    {
        Debug::error("[lenga_internal_input_get_key_down] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_input_get_key_up")) {
    function lenga_internal_input_get_key_up(int $key): bool
    {
        Debug::error("[lenga_internal_input_get_key_up] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_input_is_key_up")) {
    function lenga_internal_input_is_key_up(int $key): bool
    {
        Debug::error("[lenga_internal_input_is_key_up] Function not implemented. Key: $key");
        return false;
    }
}

if (!function_exists("lenga_internal_input_is_gamepad_available")) {
    function lenga_internal_input_is_gamepad_available(int $gamepad): bool
    {
        Debug::error("[lenga_internal_input_is_gamepad_available] Function not implemented. Gamepad: $gamepad");
        return false;
    }
}

if (!function_exists("lenga_internal_input_get_gamepad_name")) {
    function lenga_internal_input_get_gamepad_name(int $gamepad): string
    {
        Debug::error("[lenga_internal_input_get_gamepad_name] Function not implemented. Gamepad: $gamepad");
        return '';
    }
}

if (!function_exists("lenga_internal_input_is_gamepad_button_pressed")) {
    function lenga_internal_input_is_gamepad_button_pressed(int $gamepad, int $button): bool
    {
        Debug::error("[lenga_internal_input_is_gamepad_button_pressed] Function not implemented. Gamepad: $gamepad, Button: $button");
        return false;
    }
}

if (!function_exists("lenga_internal_input_is_gamepad_button_down")) {
    function lenga_internal_input_is_gamepad_button_down(int $gamepad, int $button): bool
    {
        Debug::error("[lenga_internal_input_is_gamepad_button_down] Function not implemented. Gamepad: $gamepad, Button: $button");
        return false;
    }
}

if (!function_exists("lenga_internal_input_is_gamepad_button_released")) {
    function lenga_internal_input_is_gamepad_button_released(int $gamepad, int $button): bool
    {
        Debug::error("[lenga_internal_input_is_gamepad_button_released] Function not implemented. Gamepad: $gamepad, Button: $button");
        return false;
    }
}

if (!function_exists("lenga_internal_input_is_gamepad_button_up")) {
    function lenga_internal_input_is_gamepad_button_up(int $gamepad, int $button): bool
    {
        Debug::error("[lenga_internal_input_is_gamepad_button_up] Function not implemented. Gamepad: $gamepad, Button: $button");
        return false;
    }
}

if (!function_exists("lenga_internal_input_get_gamepad_button_pressed")) {
    function lenga_internal_input_get_gamepad_button_pressed(): int
    {
        Debug::error("[lenga_internal_input_get_gamepad_button_pressed] Function not implemented.");
        return 0;
    }
}

if (!function_exists("lenga_internal_input_get_gamepad_axis_count")) {
    function lenga_internal_input_get_gamepad_axis_count(int $gamepad): int
    {
        Debug::error("[lenga_internal_input_get_gamepad_axis_count] Function not implemented. Gamepad: $gamepad");
        return 0;
    }
}

if (!function_exists("lenga_internal_input_get_gamepad_axis_movement")) {
    function lenga_internal_input_get_gamepad_axis_movement(int $gamepad, int $axis): float
    {
        Debug::error("[lenga_internal_input_get_gamepad_axis_movement] Function not implemented. Gamepad: $gamepad, Axis: $axis");
        return 0.0;
    }
}

if (!function_exists("lenga_internal_input_set_gamepad_mappings")) {
    function lenga_internal_input_set_gamepad_mappings(string $mappings): int
    {
        Debug::error("[lenga_internal_input_set_gamepad_mappings] Function not implemented.");
        return 0;
    }
}

/** --- Math Functions --- */
if (!function_exists("lenga_internal_vector3_lerp")) {
    function lenga_internal_vector3_lerp(
        float $ax,
        float $ay,
        float $az,
        float $bx,
        float $by,
        float $bz,
        float $t,
    ): array {
        $t = max(0.0, min(1.0, $t));

        return [
            'x' => $ax + (($bx - $ax) * $t),
            'y' => $ay + (($by - $ay) * $t),
            'z' => $az + (($bz - $az) * $t),
        ];
    }
}

if (!function_exists("lenga_internal_vector3_slerp")) {
    function lenga_internal_vector3_slerp(
        float $ax,
        float $ay,
        float $az,
        float $bx,
        float $by,
        float $bz,
        float $t,
    ): array {
        $t = max(0.0, min(1.0, $t));
        if ($t <= 0.0) {
            return ['x' => $ax, 'y' => $ay, 'z' => $az];
        }
        if ($t >= 1.0) {
            return ['x' => $bx, 'y' => $by, 'z' => $bz];
        }

        $magnitudeA = sqrt(($ax * $ax) + ($ay * $ay) + ($az * $az));
        $magnitudeB = sqrt(($bx * $bx) + ($by * $by) + ($bz * $bz));
        if ($magnitudeA <= 0.000001 || $magnitudeB <= 0.000001) {
            return lenga_internal_vector3_lerp($ax, $ay, $az, $bx, $by, $bz, $t);
        }

        $aNx = $ax / $magnitudeA;
        $aNy = $ay / $magnitudeA;
        $aNz = $az / $magnitudeA;
        $bNx = $bx / $magnitudeB;
        $bNy = $by / $magnitudeB;
        $bNz = $bz / $magnitudeB;

        $dot = max(-1.0, min(1.0, ($aNx * $bNx) + ($aNy * $bNy) + ($aNz * $bNz)));
        if ($dot > 0.9995) {
            $dirX = $aNx + (($bNx - $aNx) * $t);
            $dirY = $aNy + (($bNy - $aNy) * $t);
            $dirZ = $aNz + (($bNz - $aNz) * $t);
            $dirMagnitude = sqrt(($dirX * $dirX) + ($dirY * $dirY) + ($dirZ * $dirZ));
            if ($dirMagnitude > 0.000001) {
                $dirX /= $dirMagnitude;
                $dirY /= $dirMagnitude;
                $dirZ /= $dirMagnitude;
            }
        } elseif ($dot < -0.9995) {
            $axisX = abs($aNx) < 0.99 ? 1.0 : 0.0;
            $axisY = abs($aNx) < 0.99 ? 0.0 : 1.0;
            $axisZ = 0.0;
            $orthoX = ($aNy * $axisZ) - ($aNz * $axisY);
            $orthoY = ($aNz * $axisX) - ($aNx * $axisZ);
            $orthoZ = ($aNx * $axisY) - ($aNy * $axisX);
            $orthoMagnitude = sqrt(($orthoX * $orthoX) + ($orthoY * $orthoY) + ($orthoZ * $orthoZ));
            if ($orthoMagnitude <= 0.000001) {
                $orthoX = $aNy;
                $orthoY = -$aNx;
                $orthoZ = 0.0;
                $orthoMagnitude = sqrt(($orthoX * $orthoX) + ($orthoY * $orthoY) + ($orthoZ * $orthoZ));
            }
            if ($orthoMagnitude > 0.000001) {
                $orthoX /= $orthoMagnitude;
                $orthoY /= $orthoMagnitude;
                $orthoZ /= $orthoMagnitude;
            }
            $angle = 3.141592653589793 * $t;
            $dirX = ($aNx * cos($angle)) + ($orthoX * sin($angle));
            $dirY = ($aNy * cos($angle)) + ($orthoY * sin($angle));
            $dirZ = ($aNz * cos($angle)) + ($orthoZ * sin($angle));
        } else {
            $theta = acos($dot);
            $sinTheta = sin($theta);
            if (abs($sinTheta) <= 0.000001) {
                $dirX = $aNx + (($bNx - $aNx) * $t);
                $dirY = $aNy + (($bNy - $aNy) * $t);
                $dirZ = $aNz + (($bNz - $aNz) * $t);
            } else {
                $fromWeight = sin((1.0 - $t) * $theta) / $sinTheta;
                $toWeight = sin($t * $theta) / $sinTheta;
                $dirX = ($aNx * $fromWeight) + ($bNx * $toWeight);
                $dirY = ($aNy * $fromWeight) + ($bNy * $toWeight);
                $dirZ = ($aNz * $fromWeight) + ($bNz * $toWeight);
            }

            $dirMagnitude = sqrt(($dirX * $dirX) + ($dirY * $dirY) + ($dirZ * $dirZ));
            if ($dirMagnitude > 0.000001) {
                $dirX /= $dirMagnitude;
                $dirY /= $dirMagnitude;
                $dirZ /= $dirMagnitude;
            }
        }

        $magnitude = $magnitudeA + (($magnitudeB - $magnitudeA) * $t);

        return [
            'x' => $dirX * $magnitude,
            'y' => $dirY * $magnitude,
            'z' => $dirZ * $magnitude,
        ];
    }
}

if (!function_exists("lenga_internal_vector3_smooth_damp")) {
    function lenga_internal_vector3_smooth_damp(
        float $currentX,
        float $currentY,
        float $currentZ,
        float $targetX,
        float $targetY,
        float $targetZ,
        float $currentVelocityX,
        float $currentVelocityY,
        float $currentVelocityZ,
        float $smoothTime,
        float $maxSpeed = INF,
        float $deltaTime = 0.0,
    ): array {
        if ($deltaTime <= 0.0) {
            return [
                'result' => ['x' => $currentX, 'y' => $currentY, 'z' => $currentZ],
                'currentVelocity' => ['x' => $currentVelocityX, 'y' => $currentVelocityY, 'z' => $currentVelocityZ],
            ];
        }

        $smoothTime = max(0.0001, $smoothTime);
        $omega = 2.0 / $smoothTime;
        $x = $omega * $deltaTime;
        $exp = 1.0 / (1.0 + $x + 0.48 * $x * $x + 0.235 * $x * $x * $x);

        $changeX = $currentX - $targetX;
        $changeY = $currentY - $targetY;
        $changeZ = $currentZ - $targetZ;
        $originalTargetX = $targetX;
        $originalTargetY = $targetY;
        $originalTargetZ = $targetZ;

        if (is_finite($maxSpeed)) {
            $maxSpeed = max(0.0, $maxSpeed);
            $maxChange = $maxSpeed * $smoothTime;
            $maxChangeSq = $maxChange * $maxChange;
            $sqrMag = ($changeX * $changeX) + ($changeY * $changeY) + ($changeZ * $changeZ);
            if ($sqrMag > $maxChangeSq && $sqrMag > 0.0) {
                $mag = sqrt($sqrMag);
                $changeX = $changeX / $mag * $maxChange;
                $changeY = $changeY / $mag * $maxChange;
                $changeZ = $changeZ / $mag * $maxChange;
            }
        }

        $adjustedTargetX = $currentX - $changeX;
        $adjustedTargetY = $currentY - $changeY;
        $adjustedTargetZ = $currentZ - $changeZ;

        $tempX = ($currentVelocityX + $omega * $changeX) * $deltaTime;
        $tempY = ($currentVelocityY + $omega * $changeY) * $deltaTime;
        $tempZ = ($currentVelocityZ + $omega * $changeZ) * $deltaTime;

        $currentVelocityX = ($currentVelocityX - $omega * $tempX) * $exp;
        $currentVelocityY = ($currentVelocityY - $omega * $tempY) * $exp;
        $currentVelocityZ = ($currentVelocityZ - $omega * $tempZ) * $exp;

        $outputX = $adjustedTargetX + ($changeX + $tempX) * $exp;
        $outputY = $adjustedTargetY + ($changeY + $tempY) * $exp;
        $outputZ = $adjustedTargetZ + ($changeZ + $tempZ) * $exp;

        $overshoot = (($originalTargetX - $currentX) * ($outputX - $originalTargetX))
            + (($originalTargetY - $currentY) * ($outputY - $originalTargetY))
            + (($originalTargetZ - $currentZ) * ($outputZ - $originalTargetZ));
        if ($overshoot > 0.0) {
            $outputX = $originalTargetX;
            $outputY = $originalTargetY;
            $outputZ = $originalTargetZ;
            $currentVelocityX = 0.0;
            $currentVelocityY = 0.0;
            $currentVelocityZ = 0.0;
        }

        return [
            'result' => ['x' => $outputX, 'y' => $outputY, 'z' => $outputZ],
            'currentVelocity' => ['x' => $currentVelocityX, 'y' => $currentVelocityY, 'z' => $currentVelocityZ],
        ];
    }
}

if (!function_exists("lenga_internal_vector4_lerp")) {
    function lenga_internal_vector4_lerp(
        float $ax,
        float $ay,
        float $az,
        float $aw,
        float $bx,
        float $by,
        float $bz,
        float $bw,
        float $t,
    ): array {
        $t = max(0.0, min(1.0, $t));
        return [
            "x" => $ax + (($bx - $ax) * $t),
            "y" => $ay + (($by - $ay) * $t),
            "z" => $az + (($bz - $az) * $t),
            "w" => $aw + (($bw - $aw) * $t),
        ];
    }
}

if (!function_exists("lenga_internal_vector4_move_towards")) {
    function lenga_internal_vector4_move_towards(
        float $currentX,
        float $currentY,
        float $currentZ,
        float $currentW,
        float $targetX,
        float $targetY,
        float $targetZ,
        float $targetW,
        float $maxDelta,
    ): array {
        $deltaX = $targetX - $currentX;
        $deltaY = $targetY - $currentY;
        $deltaZ = $targetZ - $currentZ;
        $deltaW = $targetW - $currentW;
        $sqrDistance = ($deltaX * $deltaX) + ($deltaY * $deltaY) + ($deltaZ * $deltaZ) + ($deltaW * $deltaW);

        if ($sqrDistance <= 0.000001 || $sqrDistance <= ($maxDelta * $maxDelta)) {
            return ["x" => $targetX, "y" => $targetY, "z" => $targetZ, "w" => $targetW];
        }

        $distance = sqrt($sqrDistance);
        $scale = $maxDelta / $distance;

        return [
            "x" => $currentX + ($deltaX * $scale),
            "y" => $currentY + ($deltaY * $scale),
            "z" => $currentZ + ($deltaZ * $scale),
            "w" => $currentW + ($deltaW * $scale),
        ];
    }
}

if (!function_exists("lenga_internal_vector4_project")) {
    function lenga_internal_vector4_project(
        float $vectorX,
        float $vectorY,
        float $vectorZ,
        float $vectorW,
        float $normalX,
        float $normalY,
        float $normalZ,
        float $normalW,
    ): array {
        $denominator = ($normalX * $normalX) + ($normalY * $normalY) + ($normalZ * $normalZ) + ($normalW * $normalW);
        if ($denominator <= 0.000001) {
            return ["x" => 0.0, "y" => 0.0, "z" => 0.0, "w" => 0.0];
        }

        $scale = (($vectorX * $normalX) + ($vectorY * $normalY) + ($vectorZ * $normalZ) + ($vectorW * $normalW)) / $denominator;

        return [
            "x" => $normalX * $scale,
            "y" => $normalY * $scale,
            "z" => $normalZ * $scale,
            "w" => $normalW * $scale,
        ];
    }
}

if (!function_exists("lenga_internal_vector4_clamp_magnitude")) {
    function lenga_internal_vector4_clamp_magnitude(
        float $x,
        float $y,
        float $z,
        float $w,
        float $maxLength,
    ): array {
        $maxLength = max(0.0, $maxLength);
        $sqrMagnitude = ($x * $x) + ($y * $y) + ($z * $z) + ($w * $w);
        $maxLengthSquared = $maxLength * $maxLength;

        if ($sqrMagnitude <= $maxLengthSquared || $sqrMagnitude <= 0.000001) {
            return ["x" => $x, "y" => $y, "z" => $z, "w" => $w];
        }

        $magnitude = sqrt($sqrMagnitude);
        $scale = $maxLength / $magnitude;

        return [
            "x" => $x * $scale,
            "y" => $y * $scale,
            "z" => $z * $scale,
            "w" => $w * $scale,
        ];
    }
}
/** --- Physics2D Functions --- */
if (!function_exists("lenga_internal_physics2d_raycast")) {
    function lenga_internal_physics2d_raycast(
        float $originX,
        float $originY,
        float $originZ,
        float $directionX,
        float $directionY,
        float $directionZ,
        float $distance,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): ?array {
        Debug::error('[lenga_internal_physics2d_raycast] Function not implemented.');
        return null;
    }
}

if (!function_exists("lenga_internal_physics2d_raycast_all")) {
    function lenga_internal_physics2d_raycast_all(
        float $originX,
        float $originY,
        float $originZ,
        float $directionX,
        float $directionY,
        float $directionZ,
        float $distance,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error('[lenga_internal_physics2d_raycast_all] Function not implemented.');
        return [];
    }
}

if (!function_exists("lenga_internal_physics2d_circle_cast")) {
    function lenga_internal_physics2d_circle_cast(
        float $originX,
        float $originY,
        float $originZ,
        float $radius,
        float $directionX,
        float $directionY,
        float $directionZ,
        float $distance,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): ?array {
        Debug::error('[lenga_internal_physics2d_circle_cast] Function not implemented.');
        return null;
    }
}

if (!function_exists("lenga_internal_physics2d_circle_cast_all")) {
    function lenga_internal_physics2d_circle_cast_all(
        float $originX,
        float $originY,
        float $originZ,
        float $radius,
        float $directionX,
        float $directionY,
        float $directionZ,
        float $distance,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error('[lenga_internal_physics2d_circle_cast_all] Function not implemented.');
        return [];
    }
}

if (!function_exists("lenga_internal_physics2d_box_cast")) {
    function lenga_internal_physics2d_box_cast(
        float $originX,
        float $originY,
        float $originZ,
        float $sizeX,
        float $sizeY,
        float $sizeZ,
        float $directionX,
        float $directionY,
        float $directionZ,
        float $distance,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): ?array {
        Debug::error('[lenga_internal_physics2d_box_cast] Function not implemented.');
        return null;
    }
}

if (!function_exists("lenga_internal_physics2d_box_cast_all")) {
    function lenga_internal_physics2d_box_cast_all(
        float $originX,
        float $originY,
        float $originZ,
        float $sizeX,
        float $sizeY,
        float $sizeZ,
        float $directionX,
        float $directionY,
        float $directionZ,
        float $distance,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error('[lenga_internal_physics2d_box_cast_all] Function not implemented.');
        return [];
    }
}

if (!function_exists("lenga_internal_physics2d_overlap_point")) {
    function lenga_internal_physics2d_overlap_point(
        float $x,
        float $y,
        float $z,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): ?array {
        Debug::error('[lenga_internal_physics2d_overlap_point] Function not implemented.');
        return null;
    }
}

if (!function_exists("lenga_internal_physics2d_overlap_circle_all")) {
    function lenga_internal_physics2d_overlap_circle_all(
        float $x,
        float $y,
        float $z,
        float $radius,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error('[lenga_internal_physics2d_overlap_circle_all] Function not implemented.');
        return [];
    }
}

if (!function_exists("lenga_internal_physics2d_overlap_box_all")) {
    function lenga_internal_physics2d_overlap_box_all(
        float $x,
        float $y,
        float $z,
        float $sizeX,
        float $sizeY,
        float $sizeZ,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error('[lenga_internal_physics2d_overlap_box_all] Function not implemented.');
        return [];
    }
}

if (!function_exists("lenga_internal_physics3d_raycast")) {
    function lenga_internal_physics3d_raycast(
        float $originX,
        float $originY,
        float $originZ,
        float $directionX,
        float $directionY,
        float $directionZ,
        float $distance,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): ?array {
        Debug::error('[lenga_internal_physics3d_raycast] Function not implemented.');
        return null;
    }
}

if (!function_exists("lenga_internal_physics3d_raycast_all")) {
    function lenga_internal_physics3d_raycast_all(
        float $originX,
        float $originY,
        float $originZ,
        float $directionX,
        float $directionY,
        float $directionZ,
        float $distance,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error('[lenga_internal_physics3d_raycast_all] Function not implemented.');
        return [];
    }
}

if (!function_exists("lenga_internal_physics3d_overlap_sphere_all")) {
    function lenga_internal_physics3d_overlap_sphere_all(
        float $x,
        float $y,
        float $z,
        float $radius,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error('[lenga_internal_physics3d_overlap_sphere_all] Function not implemented.');
        return [];
    }
}

if (!function_exists("lenga_internal_physics3d_overlap_box_all")) {
    function lenga_internal_physics3d_overlap_box_all(
        float $x,
        float $y,
        float $z,
        float $sizeX,
        float $sizeY,
        float $sizeZ,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error('[lenga_internal_physics3d_overlap_box_all] Function not implemented.');
        return [];
    }
}

if (!function_exists("lenga_internal_physics3d_overlap_capsule_all")) {
    function lenga_internal_physics3d_overlap_capsule_all(
        float $x,
        float $y,
        float $z,
        float $radius,
        float $height,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error('[lenga_internal_physics3d_overlap_capsule_all] Function not implemented.');
        return [];
    }
}

/** --- Application Functions --- */
if (!function_exists("lenga_internal_engine_quit")) {
    function lenga_internal_engine_quit(): bool
    {
        Debug::error("[lenga_internal_engine_quit] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_debug_log")) {
    function lenga_internal_debug_log(string $message, ?string $level = null): bool
    {
        $prefix = match ($level) {
            'info' => 'Info',
            'warn', 'warning' => 'Warning',
            'error' => 'Error',
            default => 'Debug',
        };
        error_log($prefix . ': ' . $message);
        return true;
    }
}

/** --- Transform Functions --- */
if (!function_exists("lenga_internal_transform_translate3d")) {
    function lenga_internal_transform_translate3d(float $dx, float $dy, float $dz): bool
    {
        Debug::error("[lenga_internal_transform_translate3d] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_position3d")) {
    function lenga_internal_transform_get_position3d(): array|false
    {
        Debug::error("[lenga_internal_transform_get_position3d] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_position3d")) {
    function lenga_internal_transform_set_position3d(float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_transform_set_position3d] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_translate3d_by_id")) {
    function lenga_internal_transform_translate3d_by_id(int $transformId, float $dx, float $dy, float $dz): bool
    {
        Debug::error("[lenga_internal_transform_translate3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_position3d_by_id")) {
    function lenga_internal_transform_get_position3d_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_position3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_position3d_by_id")) {
    function lenga_internal_transform_set_position3d_by_id(int $transformId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_transform_set_position3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

/** --- GameObject Functions --- */
if (!function_exists("lenga_internal_game_object_set_active")) {
    function lenga_internal_game_object_set_active(string $name, bool $active): bool
    {
        Debug::error("[lenga_internal_game_object_set_active] Function not implemented. Name: $name");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_find_by_name")) {
    function lenga_internal_game_object_find_by_name(string $name): array|false
    {
        Debug::error("[lenga_internal_game_object_find_by_name] Function not implemented. Name: $name");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_find_by_scene_id")) {
    function lenga_internal_game_object_find_by_scene_id(string $sceneObjectId): array|false
    {
        Debug::error("[lenga_internal_game_object_find_by_scene_id] Function not implemented. SceneObjectId: $sceneObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_lookup_by_id")) {
    function lenga_internal_game_object_lookup_by_id(int $gameObjectId): array|false
    {
        Debug::error("[lenga_internal_game_object_lookup_by_id] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_find_with_tag")) {
    function lenga_internal_game_object_find_with_tag(string $tag): array|false
    {
        Debug::error("[lenga_internal_game_object_find_with_tag] Function not implemented. Tag: $tag");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_find_game_objects_with_tag")) {
    function lenga_internal_game_object_find_game_objects_with_tag(string $tag): array
    {
        Debug::error("[lenga_internal_game_object_find_game_objects_with_tag] Function not implemented. Tag: $tag");
        return [];
    }
}

if (!function_exists("lenga_internal_game_object_get_name")) {
    function lenga_internal_game_object_get_name(int $gameObjectId): string
    {
        Debug::error("[lenga_internal_game_object_get_name] Function not implemented. GameObjectId: $gameObjectId");
        return '';
    }
}

if (!function_exists("lenga_internal_game_object_set_name")) {
    function lenga_internal_game_object_set_name(int $gameObjectId, string $name): bool
    {
        Debug::error("[lenga_internal_game_object_set_name] Function not implemented. GameObjectId: $gameObjectId, Name: $name");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_get_tag")) {
    function lenga_internal_game_object_get_tag(int $gameObjectId): string
    {
        Debug::error("[lenga_internal_game_object_get_tag] Function not implemented. GameObjectId: $gameObjectId");
        return 'Untagged';
    }
}

if (!function_exists("lenga_internal_game_object_set_tag")) {
    function lenga_internal_game_object_set_tag(int $gameObjectId, string $tag): bool
    {
        Debug::error("[lenga_internal_game_object_set_tag] Function not implemented. GameObjectId: $gameObjectId, Tag: $tag");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_get_layer")) {
    function lenga_internal_game_object_get_layer(int $gameObjectId): int
    {
        Debug::error("[lenga_internal_game_object_get_layer] Function not implemented. GameObjectId: $gameObjectId");
        return 0;
    }
}

if (!function_exists("lenga_internal_game_object_set_layer")) {
    function lenga_internal_game_object_set_layer(int $gameObjectId, int $layer): bool
    {
        Debug::error("[lenga_internal_game_object_set_layer] Function not implemented. GameObjectId: $gameObjectId, Layer: $layer");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_get_active_self")) {
    function lenga_internal_game_object_get_active_self(int $gameObjectId): bool
    {
        Debug::error("[lenga_internal_game_object_get_active_self] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_get_active_in_hierarchy")) {
    function lenga_internal_game_object_get_active_in_hierarchy(int $gameObjectId): bool
    {
        Debug::error("[lenga_internal_game_object_get_active_in_hierarchy] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_set_active_by_id")) {
    function lenga_internal_game_object_set_active_by_id(int $gameObjectId, bool $active): bool
    {
        Debug::error("[lenga_internal_game_object_set_active_by_id] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_local_position3d_by_id")) {
    function lenga_internal_transform_get_local_position3d_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_local_position3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_local_position3d_by_id")) {
    function lenga_internal_transform_set_local_position3d_by_id(int $transformId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_transform_set_local_position3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_euler_angles3d_by_id")) {
    function lenga_internal_transform_get_euler_angles3d_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_euler_angles3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_euler_angles3d_by_id")) {
    function lenga_internal_transform_set_euler_angles3d_by_id(int $transformId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_transform_set_euler_angles3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_local_euler_angles3d_by_id")) {
    function lenga_internal_transform_get_local_euler_angles3d_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_local_euler_angles3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_local_euler_angles3d_by_id")) {
    function lenga_internal_transform_set_local_euler_angles3d_by_id(int $transformId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_transform_set_local_euler_angles3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_rotation3d_by_id")) {
    function lenga_internal_transform_get_rotation3d_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_rotation3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_rotation3d_by_id")) {
    function lenga_internal_transform_set_rotation3d_by_id(int $transformId, float $x, float $y, float $z, float $w): bool
    {
        Debug::error("[lenga_internal_transform_set_rotation3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_local_rotation3d_by_id")) {
    function lenga_internal_transform_get_local_rotation3d_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_local_rotation3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_local_rotation3d_by_id")) {
    function lenga_internal_transform_set_local_rotation3d_by_id(int $transformId, float $x, float $y, float $z, float $w): bool
    {
        Debug::error("[lenga_internal_transform_set_local_rotation3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_rotate3d_by_id")) {
    function lenga_internal_transform_rotate3d_by_id(int $transformId, float $x, float $y, float $z, bool $relativeToSelf): bool
    {
        Debug::error("[lenga_internal_transform_rotate3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_scale3d_by_id")) {
    function lenga_internal_transform_get_scale3d_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_scale3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_scale3d_by_id")) {
    function lenga_internal_transform_set_scale3d_by_id(int $transformId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_transform_set_scale3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_local_scale3d_by_id")) {
    function lenga_internal_transform_get_local_scale3d_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_local_scale3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_local_scale3d_by_id")) {
    function lenga_internal_transform_set_local_scale3d_by_id(int $transformId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_transform_set_local_scale3d_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_look_at_by_id")) {
    function lenga_internal_transform_look_at_by_id(int $transformId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_transform_look_at_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_parent_by_id")) {
    function lenga_internal_transform_get_parent_by_id(int $transformId): ?int
    {
        Debug::error("[lenga_internal_transform_get_parent_by_id] Function not implemented. TransformId: $transformId");
        return null;
    }
}

if (!function_exists("lenga_internal_transform_get_children_by_id")) {
    function lenga_internal_transform_get_children_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_children_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_get_game_object_by_id")) {
    function lenga_internal_transform_get_game_object_by_id(int $transformId): array|false
    {
        Debug::error("[lenga_internal_transform_get_game_object_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_transform_set_parent_by_id")) {
    function lenga_internal_transform_set_parent_by_id(int $transformId, ?int $parentTransformId, bool $worldPositionStays): bool
    {
        Debug::error("[lenga_internal_transform_set_parent_by_id] Function not implemented. TransformId: $transformId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_get_scene_by_id")) {
    function lenga_internal_game_object_get_scene_by_id(int $gameObjectId): array|false
    {
        Debug::error("[lenga_internal_game_object_get_scene_by_id] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_get_parent_by_id")) {
    function lenga_internal_game_object_get_parent_by_id(int $gameObjectId): array|false
    {
        Debug::error("[lenga_internal_game_object_get_parent_by_id] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_get_children_by_id")) {
    function lenga_internal_game_object_get_children_by_id(int $gameObjectId): array|false
    {
        Debug::error("[lenga_internal_game_object_get_children_by_id] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_set_parent_by_id")) {
    function lenga_internal_game_object_set_parent_by_id(int $gameObjectId, ?int $parentGameObjectId, bool $worldPositionStays): bool
    {
        Debug::error("[lenga_internal_game_object_set_parent_by_id] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_destroy_by_id")) {
    function lenga_internal_game_object_destroy_by_id(int $gameObjectId): bool
    {
        Debug::error("[lenga_internal_game_object_destroy_by_id] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_has_component_by_id")) {
    function lenga_internal_game_object_has_component_by_id(
        int $gameObjectId,
        string $componentType,
        ?string $scriptClass = null,
    ): bool {
        Debug::error("[lenga_internal_game_object_has_component_by_id] Function not implemented. GameObjectId: $gameObjectId, Type: $componentType");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_get_component_by_id")) {
    function lenga_internal_game_object_get_component_by_id(
        int $gameObjectId,
        string $componentType,
        ?string $scriptClass = null,
    ): mixed {
        Debug::error("[lenga_internal_game_object_get_component_by_id] Function not implemented. GameObjectId: $gameObjectId, Type: $componentType");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_get_components_by_id")) {
    function lenga_internal_game_object_get_components_by_id(
        int $gameObjectId,
        ?string $componentType = null,
        ?string $scriptClass = null,
    ): array|false {
        Debug::error("[lenga_internal_game_object_get_components_by_id] Function not implemented. GameObjectId: $gameObjectId, Type: " . ($componentType ?? 'null'));
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_add_component_by_id")) {
    function lenga_internal_game_object_add_component_by_id(
        int $gameObjectId,
        string $componentType,
        ?string $scriptClass = null,
    ): mixed {
        Debug::error("[lenga_internal_game_object_add_component_by_id] Function not implemented. GameObjectId: $gameObjectId, Type: $componentType");
        return false;
    }
}

if (!function_exists("lenga_internal_game_object_instantiate_by_id")) {
    function lenga_internal_game_object_instantiate_by_id(int $gameObjectId, ?string $name = null): array|false
    {
        Debug::error("[lenga_internal_game_object_instantiate_by_id] Function not implemented. GameObjectId: $gameObjectId");
        return false;
    }
}

if (!function_exists("lenga_internal_scene_get_active")) {
    function lenga_internal_scene_get_active(): array|false
    {
        Debug::error("[lenga_internal_scene_get_active] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_scene_load")) {
    function lenga_internal_scene_load(string $sceneNameOrPath): bool
    {
        Debug::error("[lenga_internal_scene_load] Function not implemented. Scene: $sceneNameOrPath");
        return false;
    }
}

if (!function_exists("lenga_internal_scene_create_game_object")) {
    function lenga_internal_scene_create_game_object(string $name): array|false
    {
        Debug::error("[lenga_internal_scene_create_game_object] Function not implemented. Name: $name");
        return false;
    }
}

if (!function_exists("lenga_internal_scene_get_canvases")) {
    function lenga_internal_scene_get_canvases(): array|false
    {
        Debug::error("[lenga_internal_scene_get_canvases] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_scene_instantiate_prefab")) {
    function lenga_internal_scene_instantiate_prefab(string $assetPath, ?string $name = null): array|false
    {
        Debug::error("[lenga_internal_scene_instantiate_prefab] Function not implemented. AssetPath: $assetPath");
        return false;
    }
}

if (!function_exists("lenga_internal_scene_create_canvas")) {
    function lenga_internal_scene_create_canvas(string $name): array|false
    {
        Debug::error("[lenga_internal_scene_create_canvas] Function not implemented. Name: $name");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_get_state")) {
    function lenga_internal_ui_canvas_get_state(int $canvasId): array|false
    {
        Debug::error("[lenga_internal_ui_canvas_get_state] Function not implemented. CanvasId: $canvasId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_set_enabled")) {
    function lenga_internal_ui_canvas_set_enabled(int $canvasId, bool $value): bool
    {
        Debug::error("[lenga_internal_ui_canvas_set_enabled] Function not implemented. CanvasId: $canvasId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_set_visible")) {
    function lenga_internal_ui_canvas_set_visible(int $canvasId, bool $value): bool
    {
        Debug::error("[lenga_internal_ui_canvas_set_visible] Function not implemented. CanvasId: $canvasId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_set_sort_order")) {
    function lenga_internal_ui_canvas_set_sort_order(int $canvasId, int $value): bool
    {
        Debug::error("[lenga_internal_ui_canvas_set_sort_order] Function not implemented. CanvasId: $canvasId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_set_render_mode")) {
    function lenga_internal_ui_canvas_set_render_mode(int $canvasId, string $value): bool
    {
        Debug::error("[lenga_internal_ui_canvas_set_render_mode] Function not implemented. CanvasId: $canvasId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_set_reference_resolution")) {
    function lenga_internal_ui_canvas_set_reference_resolution(int $canvasId, float $width, float $height): bool
    {
        Debug::error("[lenga_internal_ui_canvas_set_reference_resolution] Function not implemented. CanvasId: $canvasId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_set_match_width_or_height")) {
    function lenga_internal_ui_canvas_set_match_width_or_height(int $canvasId, float $value): bool
    {
        Debug::error("[lenga_internal_ui_canvas_set_match_width_or_height] Function not implemented. CanvasId: $canvasId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_create_text")) {
    function lenga_internal_ui_canvas_create_text(int $canvasId, string $name, ?int $parentElementId = null): array|false
    {
        Debug::error("[lenga_internal_ui_canvas_create_text] Function not implemented. CanvasId: $canvasId, Name: $name");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_create_image")) {
    function lenga_internal_ui_canvas_create_image(int $canvasId, string $name, ?int $parentElementId = null): array|false
    {
        Debug::error("[lenga_internal_ui_canvas_create_image] Function not implemented. CanvasId: $canvasId, Name: $name");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_create_button")) {
    function lenga_internal_ui_canvas_create_button(int $canvasId, string $name, ?int $parentElementId = null): array|false
    {
        Debug::error("[lenga_internal_ui_canvas_create_button] Function not implemented. CanvasId: $canvasId, Name: $name");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_canvas_get_root_elements")) {
    function lenga_internal_ui_canvas_get_root_elements(int $canvasId): array|false
    {
        Debug::error("[lenga_internal_ui_canvas_get_root_elements] Function not implemented. CanvasId: $canvasId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_element_get_state")) {
    function lenga_internal_ui_element_get_state(int $elementId): array|false
    {
        Debug::error("[lenga_internal_ui_element_get_state] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_element_set_enabled")) {
    function lenga_internal_ui_element_set_enabled(int $elementId, bool $value): bool
    {
        Debug::error("[lenga_internal_ui_element_set_enabled] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_element_set_visible")) {
    function lenga_internal_ui_element_set_visible(int $elementId, bool $value): bool
    {
        Debug::error("[lenga_internal_ui_element_set_visible] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_element_set_sort_order")) {
    function lenga_internal_ui_element_set_sort_order(int $elementId, int $value): bool
    {
        Debug::error("[lenga_internal_ui_element_set_sort_order] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_element_get_parent")) {
    function lenga_internal_ui_element_get_parent(int $elementId): array|false|null
    {
        Debug::error("[lenga_internal_ui_element_get_parent] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_element_set_parent")) {
    function lenga_internal_ui_element_set_parent(int $elementId, ?int $parentElementId): bool
    {
        Debug::error("[lenga_internal_ui_element_set_parent] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_element_get_children")) {
    function lenga_internal_ui_element_get_children(int $elementId): array|false
    {
        Debug::error("[lenga_internal_ui_element_get_children] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_rect_transform_get_state")) {
    function lenga_internal_ui_rect_transform_get_state(int $elementId): array|false
    {
        Debug::error("[lenga_internal_ui_rect_transform_get_state] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_rect_transform_set_anchor_min")) {
    function lenga_internal_ui_rect_transform_set_anchor_min(int $elementId, float $x, float $y): bool
    {
        Debug::error("[lenga_internal_ui_rect_transform_set_anchor_min] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_rect_transform_set_anchor_max")) {
    function lenga_internal_ui_rect_transform_set_anchor_max(int $elementId, float $x, float $y): bool
    {
        Debug::error("[lenga_internal_ui_rect_transform_set_anchor_max] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_rect_transform_set_pivot")) {
    function lenga_internal_ui_rect_transform_set_pivot(int $elementId, float $x, float $y): bool
    {
        Debug::error("[lenga_internal_ui_rect_transform_set_pivot] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_rect_transform_set_anchored_position")) {
    function lenga_internal_ui_rect_transform_set_anchored_position(int $elementId, float $x, float $y): bool
    {
        Debug::error("[lenga_internal_ui_rect_transform_set_anchored_position] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_rect_transform_set_size_delta")) {
    function lenga_internal_ui_rect_transform_set_size_delta(int $elementId, float $x, float $y): bool
    {
        Debug::error("[lenga_internal_ui_rect_transform_set_size_delta] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_rect_transform_set_rotation")) {
    function lenga_internal_ui_rect_transform_set_rotation(int $elementId, float $rotation): bool
    {
        Debug::error("[lenga_internal_ui_rect_transform_set_rotation] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_rect_transform_set_scale")) {
    function lenga_internal_ui_rect_transform_set_scale(int $elementId, float $x, float $y): bool
    {
        Debug::error("[lenga_internal_ui_rect_transform_set_scale] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_text_set_text")) {
    function lenga_internal_ui_text_set_text(int $elementId, string $value): bool
    {
        Debug::error("[lenga_internal_ui_text_set_text] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_text_set_font_size")) {
    function lenga_internal_ui_text_set_font_size(int $elementId, float $value): bool
    {
        Debug::error("[lenga_internal_ui_text_set_font_size] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_text_set_font_path")) {
    function lenga_internal_ui_text_set_font_path(int $elementId, string $value): bool
    {
        Debug::error("[lenga_internal_ui_text_set_font_path] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_text_set_color")) {
    function lenga_internal_ui_text_set_color(int $elementId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_ui_text_set_color] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_text_set_use_sdf")) {
    function lenga_internal_ui_text_set_use_sdf(int $elementId, bool $value): bool
    {
        Debug::error("[lenga_internal_ui_text_set_use_sdf] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_text_set_sdf_outline_width")) {
    function lenga_internal_ui_text_set_sdf_outline_width(int $elementId, float $value): bool
    {
        Debug::error("[lenga_internal_ui_text_set_sdf_outline_width] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_text_set_sdf_softness")) {
    function lenga_internal_ui_text_set_sdf_softness(int $elementId, float $value): bool
    {
        Debug::error("[lenga_internal_ui_text_set_sdf_softness] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_image_set_sprite_path")) {
    function lenga_internal_ui_image_set_sprite_path(int $elementId, string $value): bool
    {
        Debug::error("[lenga_internal_ui_image_set_sprite_path] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_image_set_color")) {
    function lenga_internal_ui_image_set_color(int $elementId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_ui_image_set_color] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_text")) {
    function lenga_internal_ui_button_set_text(int $elementId, string $value): bool
    {
        Debug::error("[lenga_internal_ui_button_set_text] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_font_size")) {
    function lenga_internal_ui_button_set_font_size(int $elementId, float $value): bool
    {
        Debug::error("[lenga_internal_ui_button_set_font_size] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_font_path")) {
    function lenga_internal_ui_button_set_font_path(int $elementId, string $value): bool
    {
        Debug::error("[lenga_internal_ui_button_set_font_path] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_interactable")) {
    function lenga_internal_ui_button_set_interactable(int $elementId, bool $value): bool
    {
        Debug::error("[lenga_internal_ui_button_set_interactable] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_text_color")) {
    function lenga_internal_ui_button_set_text_color(int $elementId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_ui_button_set_text_color] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_background_color")) {
    function lenga_internal_ui_button_set_background_color(int $elementId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_ui_button_set_background_color] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_background_image")) {
    function lenga_internal_ui_button_set_background_image(int $elementId, string $filename): bool
    {
        Debug::error("[lenga_internal_ui_button_set_background_image] Function not implemented. ElementId: $elementId, Filename: $filename");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_hover_image")) {
    function lenga_internal_ui_button_set_hover_image(int $elementId, string $filename): bool
    {
        Debug::error("[lenga_internal_ui_button_set_hover_image] Function not implemented. ElementId: $elementId, Filename: $filename");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_pressed_image")) {
    function lenga_internal_ui_button_set_pressed_image(int $elementId, string $filename): bool
    {
        Debug::error("[lenga_internal_ui_button_set_pressed_image] Function not implemented. ElementId: $elementId, Filename: $filename");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_disabled_image")) {
    function lenga_internal_ui_button_set_disabled_image(int $elementId, string $filename): bool
    {
        Debug::error("[lenga_internal_ui_button_set_disabled_image] Function not implemented. ElementId: $elementId, Filename: $filename");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_hover_color")) {
    function lenga_internal_ui_button_set_hover_color(int $elementId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_ui_button_set_hover_color] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_pressed_color")) {
    function lenga_internal_ui_button_set_pressed_color(int $elementId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_ui_button_set_pressed_color] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_ui_button_set_disabled_color")) {
    function lenga_internal_ui_button_set_disabled_color(int $elementId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_ui_button_set_disabled_color] Function not implemented. ElementId: $elementId");
        return false;
    }
}

if (!function_exists("lenga_internal_component_get_enabled")) {
    function lenga_internal_component_get_enabled(int $componentId): bool
    {
        Debug::error("[lenga_internal_component_get_enabled] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_component_set_enabled")) {
    function lenga_internal_component_set_enabled(int $componentId, bool $enabled): bool
    {
        Debug::error("[lenga_internal_component_set_enabled] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rectangle_renderer_get_state")) {
    function lenga_internal_rectangle_renderer_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_rectangle_renderer_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rectangle_renderer_set_size")) {
    function lenga_internal_rectangle_renderer_set_size(int $componentId, float $width, float $height): bool
    {
        Debug::error("[lenga_internal_rectangle_renderer_set_size] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rectangle_renderer_set_color")) {
    function lenga_internal_rectangle_renderer_set_color(int $componentId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_rectangle_renderer_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rectangle_renderer_set_sorting_layer")) {
    function lenga_internal_rectangle_renderer_set_sorting_layer(int $componentId, string $sortingLayer): bool
    {
        Debug::error("[lenga_internal_rectangle_renderer_set_sorting_layer] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rectangle_renderer_set_order_in_layer")) {
    function lenga_internal_rectangle_renderer_set_order_in_layer(int $componentId, int $orderInLayer): bool
    {
        Debug::error("[lenga_internal_rectangle_renderer_set_order_in_layer] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_renderer_get_state")) {
    function lenga_internal_sprite_renderer_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_sprite_renderer_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_renderer_set_size")) {
    function lenga_internal_sprite_renderer_set_size(int $componentId, float $width, float $height): bool
    {
        Debug::error("[lenga_internal_sprite_renderer_set_size] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_renderer_set_pivot")) {
    function lenga_internal_sprite_renderer_set_pivot(int $componentId, float $x, float $y): bool
    {
        Debug::error("[lenga_internal_sprite_renderer_set_pivot] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_renderer_set_color")) {
    function lenga_internal_sprite_renderer_set_color(int $componentId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_sprite_renderer_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_renderer_set_sorting_layer")) {
    function lenga_internal_sprite_renderer_set_sorting_layer(int $componentId, string $sortingLayer): bool
    {
        Debug::error("[lenga_internal_sprite_renderer_set_sorting_layer] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_renderer_set_order_in_layer")) {
    function lenga_internal_sprite_renderer_set_order_in_layer(int $componentId, int $orderInLayer): bool
    {
        Debug::error("[lenga_internal_sprite_renderer_set_order_in_layer] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_renderer_load_texture")) {
    function lenga_internal_sprite_renderer_load_texture(int $componentId, string $texturePath): bool
    {
        Debug::error("[lenga_internal_sprite_renderer_load_texture] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_renderer_set_flip_x")) {
    function lenga_internal_sprite_renderer_set_flip_x(int $componentId, bool $flipX): bool
    {
        Debug::error("[lenga_internal_sprite_renderer_set_flip_x] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_renderer_set_flip_y")) {
    function lenga_internal_sprite_renderer_set_flip_y(int $componentId, bool $flipY): bool
    {
        Debug::error("[lenga_internal_sprite_renderer_set_flip_y] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_particle_system_get_state")) {
    function lenga_internal_particle_system_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_particle_system_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_particle_system_play")) {
    function lenga_internal_particle_system_play(int $componentId): bool
    {
        Debug::error("[lenga_internal_particle_system_play] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_particle_system_stop")) {
    function lenga_internal_particle_system_stop(int $componentId, bool $clear): bool
    {
        Debug::error("[lenga_internal_particle_system_stop] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_particle_system_clear")) {
    function lenga_internal_particle_system_clear(int $componentId): bool
    {
        Debug::error("[lenga_internal_particle_system_clear] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_particle_system_emit")) {
    function lenga_internal_particle_system_emit(int $componentId, int $count): bool
    {
        Debug::error("[lenga_internal_particle_system_emit] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_particle_system_load_texture")) {
    function lenga_internal_particle_system_load_texture(int $componentId, string $texturePath): bool
    {
        Debug::error("[lenga_internal_particle_system_load_texture] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_particle_system_set_sorting_layer")) {
    function lenga_internal_particle_system_set_sorting_layer(int $componentId, string $sortingLayer): bool
    {
        Debug::error("[lenga_internal_particle_system_set_sorting_layer] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_particle_system_set_order_in_layer")) {
    function lenga_internal_particle_system_set_order_in_layer(int $componentId, int $orderInLayer): bool
    {
        Debug::error("[lenga_internal_particle_system_set_order_in_layer] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_camera_get_state")) {
    function lenga_internal_camera_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_camera_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_camera_set_primary")) {
    function lenga_internal_camera_set_primary(int $componentId, bool $primary): bool
    {
        Debug::error("[lenga_internal_camera_set_primary] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_camera_set_projection")) {
    function lenga_internal_camera_set_projection(int $componentId, string $projection): bool
    {
        Debug::error("[lenga_internal_camera_set_projection] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_camera_set_field_of_view")) {
    function lenga_internal_camera_set_field_of_view(int $componentId, float $fieldOfView): bool
    {
        Debug::error("[lenga_internal_camera_set_field_of_view] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_camera_set_orthographic_size")) {
    function lenga_internal_camera_set_orthographic_size(int $componentId, float $orthographicSize): bool
    {
        Debug::error("[lenga_internal_camera_set_orthographic_size] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_directional_light_get_state")) {
    function lenga_internal_directional_light_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_directional_light_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_directional_light_set_color")) {
    function lenga_internal_directional_light_set_color(int $componentId, int $red, int $green, int $blue, int $alpha = 255): bool
    {
        Debug::error("[lenga_internal_directional_light_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_directional_light_set_intensity")) {
    function lenga_internal_directional_light_set_intensity(int $componentId, float $intensity): bool
    {
        Debug::error("[lenga_internal_directional_light_set_intensity] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_directional_light_set_shadows_enabled")) {
    function lenga_internal_directional_light_set_shadows_enabled(int $componentId, bool $enabled): bool
    {
        Debug::error("[lenga_internal_directional_light_set_shadows_enabled] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_directional_light_set_shadow_strength")) {
    function lenga_internal_directional_light_set_shadow_strength(int $componentId, float $strength): bool
    {
        Debug::error("[lenga_internal_directional_light_set_shadow_strength] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_directional_light_set_shadow_bias")) {
    function lenga_internal_directional_light_set_shadow_bias(int $componentId, float $bias): bool
    {
        Debug::error("[lenga_internal_directional_light_set_shadow_bias] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_directional_light_set_shadow_projection_size")) {
    function lenga_internal_directional_light_set_shadow_projection_size(int $componentId, float $size): bool
    {
        Debug::error("[lenga_internal_directional_light_set_shadow_projection_size] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_directional_light_set_shadow_distance")) {
    function lenga_internal_directional_light_set_shadow_distance(int $componentId, float $distance): bool
    {
        Debug::error("[lenga_internal_directional_light_set_shadow_distance] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_point_light_get_state")) {
    function lenga_internal_point_light_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_point_light_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_point_light_set_color")) {
    function lenga_internal_point_light_set_color(int $componentId, int $red, int $green, int $blue, int $alpha = 255): bool
    {
        Debug::error("[lenga_internal_point_light_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_point_light_set_intensity")) {
    function lenga_internal_point_light_set_intensity(int $componentId, float $intensity): bool
    {
        Debug::error("[lenga_internal_point_light_set_intensity] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_point_light_set_range")) {
    function lenga_internal_point_light_set_range(int $componentId, float $range): bool
    {
        Debug::error("[lenga_internal_point_light_set_range] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cube_renderer_get_state")) {
    function lenga_internal_cube_renderer_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_cube_renderer_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cube_renderer_set_size")) {
    function lenga_internal_cube_renderer_set_size(int $componentId, float $width, float $height, float $length): bool
    {
        Debug::error("[lenga_internal_cube_renderer_set_size] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cube_renderer_set_color")) {
    function lenga_internal_cube_renderer_set_color(int $componentId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_cube_renderer_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cube_renderer_set_material_path")) {
    function lenga_internal_cube_renderer_set_material_path(int $componentId, string $materialPath): bool
    {
        Debug::error("[lenga_internal_cube_renderer_set_material_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_renderer_get_state")) {
    function lenga_internal_sphere_renderer_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_sphere_renderer_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_renderer_set_center")) {
    function lenga_internal_sphere_renderer_set_center(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_sphere_renderer_set_center] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_renderer_set_radius")) {
    function lenga_internal_sphere_renderer_set_radius(int $componentId, float $radius): bool
    {
        Debug::error("[lenga_internal_sphere_renderer_set_radius] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_renderer_set_color")) {
    function lenga_internal_sphere_renderer_set_color(int $componentId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_sphere_renderer_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_renderer_set_material_path")) {
    function lenga_internal_sphere_renderer_set_material_path(int $componentId, string $materialPath): bool
    {
        Debug::error("[lenga_internal_sphere_renderer_set_material_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cylinder_renderer_get_state")) {
    function lenga_internal_cylinder_renderer_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_cylinder_renderer_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cylinder_renderer_set_position")) {
    function lenga_internal_cylinder_renderer_set_position(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_cylinder_renderer_set_position] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cylinder_renderer_set_top_radius")) {
    function lenga_internal_cylinder_renderer_set_top_radius(int $componentId, float $radius): bool
    {
        Debug::error("[lenga_internal_cylinder_renderer_set_top_radius] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cylinder_renderer_set_bottom_radius")) {
    function lenga_internal_cylinder_renderer_set_bottom_radius(int $componentId, float $radius): bool
    {
        Debug::error("[lenga_internal_cylinder_renderer_set_bottom_radius] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cylinder_renderer_set_height")) {
    function lenga_internal_cylinder_renderer_set_height(int $componentId, float $height): bool
    {
        Debug::error("[lenga_internal_cylinder_renderer_set_height] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cylinder_renderer_set_total_slices")) {
    function lenga_internal_cylinder_renderer_set_total_slices(int $componentId, int $totalSlices): bool
    {
        Debug::error("[lenga_internal_cylinder_renderer_set_total_slices] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cylinder_renderer_set_color")) {
    function lenga_internal_cylinder_renderer_set_color(int $componentId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_cylinder_renderer_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_cylinder_renderer_set_material_path")) {
    function lenga_internal_cylinder_renderer_set_material_path(int $componentId, string $materialPath): bool
    {
        Debug::error("[lenga_internal_cylinder_renderer_set_material_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_plane_renderer_get_state")) {
    function lenga_internal_plane_renderer_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_plane_renderer_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_plane_renderer_set_size")) {
    function lenga_internal_plane_renderer_set_size(int $componentId, float $width, float $length): bool
    {
        Debug::error("[lenga_internal_plane_renderer_set_size] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_plane_renderer_set_color")) {
    function lenga_internal_plane_renderer_set_color(int $componentId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_plane_renderer_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_plane_renderer_set_material_path")) {
    function lenga_internal_plane_renderer_set_material_path(int $componentId, string $materialPath): bool
    {
        Debug::error("[lenga_internal_plane_renderer_set_material_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_mesh_renderer_get_state")) {
    function lenga_internal_mesh_renderer_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_mesh_renderer_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_mesh_renderer_load_mesh")) {
    function lenga_internal_mesh_renderer_load_mesh(int $componentId, string $meshPath): bool
    {
        Debug::error("[lenga_internal_mesh_renderer_load_mesh] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_mesh_renderer_set_color")) {
    function lenga_internal_mesh_renderer_set_color(int $componentId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_mesh_renderer_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_mesh_renderer_set_material_path")) {
    function lenga_internal_mesh_renderer_set_material_path(int $componentId, string $materialPath): bool
    {
        Debug::error("[lenga_internal_mesh_renderer_set_material_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_get_state")) {
    function lenga_internal_model_renderer_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_model_renderer_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_load_model")) {
    function lenga_internal_model_renderer_load_model(int $componentId, string $modelPath): bool
    {
        Debug::error("[lenga_internal_model_renderer_load_model] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_set_animation_path")) {
    function lenga_internal_model_renderer_set_animation_path(int $componentId, string $animationPath): bool
    {
        Debug::error("[lenga_internal_model_renderer_set_animation_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_set_animation_index")) {
    function lenga_internal_model_renderer_set_animation_index(int $componentId, int $animationIndex): bool
    {
        Debug::error("[lenga_internal_model_renderer_set_animation_index] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_set_play_on_awake")) {
    function lenga_internal_model_renderer_set_play_on_awake(int $componentId, bool $playOnAwake): bool
    {
        Debug::error("[lenga_internal_model_renderer_set_play_on_awake] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_set_loop")) {
    function lenga_internal_model_renderer_set_loop(int $componentId, bool $loop): bool
    {
        Debug::error("[lenga_internal_model_renderer_set_loop] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_set_speed")) {
    function lenga_internal_model_renderer_set_speed(int $componentId, float $speed): bool
    {
        Debug::error("[lenga_internal_model_renderer_set_speed] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_set_playback_fps")) {
    function lenga_internal_model_renderer_set_playback_fps(int $componentId, float $playbackFps): bool
    {
        Debug::error("[lenga_internal_model_renderer_set_playback_fps] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_play")) {
    function lenga_internal_model_renderer_play(int $componentId): bool
    {
        Debug::error("[lenga_internal_model_renderer_play] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_stop")) {
    function lenga_internal_model_renderer_stop(int $componentId): bool
    {
        Debug::error("[lenga_internal_model_renderer_stop] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_set_color")) {
    function lenga_internal_model_renderer_set_color(int $componentId, int $red, int $green, int $blue, int $alpha): bool
    {
        Debug::error("[lenga_internal_model_renderer_set_color] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_model_renderer_set_material_path")) {
    function lenga_internal_model_renderer_set_material_path(int $componentId, string $materialPath): bool
    {
        Debug::error("[lenga_internal_model_renderer_set_material_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_get_state")) {
    function lenga_internal_rigidbody2d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_rigidbody2d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_add_force")) {
    function lenga_internal_rigidbody2d_add_force(int $componentId, float $x, float $y, int $forceMode): bool
    {
        Debug::error("[lenga_internal_rigidbody2d_add_force] Function not implemented.");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_get_velocity")) {
    function lenga_internal_rigidbody2d_get_velocity(int $componentId): array|false
    {
        Debug::error("[lenga_internal_rigidbody2d_get_velocity] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_set_velocity")) {
    function lenga_internal_rigidbody2d_set_velocity(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_rigidbody2d_set_velocity] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_set_body_type")) {
    function lenga_internal_rigidbody2d_set_body_type(int $componentId, string $bodyType): bool
    {
        Debug::error("[lenga_internal_rigidbody2d_set_body_type] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_set_use_gravity")) {
    function lenga_internal_rigidbody2d_set_use_gravity(int $componentId, bool $useGravity): bool
    {
        Debug::error("[lenga_internal_rigidbody2d_set_use_gravity] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_set_gravity_scale")) {
    function lenga_internal_rigidbody2d_set_gravity_scale(int $componentId, float $gravityScale): bool
    {
        Debug::error("[lenga_internal_rigidbody2d_set_gravity_scale] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_set_linear_drag")) {
    function lenga_internal_rigidbody2d_set_linear_drag(int $componentId, float $linearDrag): bool
    {
        Debug::error("[lenga_internal_rigidbody2d_set_linear_drag] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_set_freeze_rotation")) {
    function lenga_internal_rigidbody2d_set_freeze_rotation(int $componentId, bool $freezeRotation): bool
    {
        Debug::error("[lenga_internal_rigidbody2d_set_freeze_rotation] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_set_collision_detection")) {
    function lenga_internal_rigidbody2d_set_collision_detection(int $componentId, string $collisionDetection): bool
    {
        Debug::error("[lenga_internal_rigidbody2d_set_collision_detection] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_is_touching")) {
    function lenga_internal_rigidbody2d_is_touching(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): bool {
        Debug::error("[lenga_internal_rigidbody2d_is_touching] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_is_grounded")) {
    function lenga_internal_rigidbody2d_is_grounded(
        int $componentId,
        float $minSupportDot = 0.5,
        bool $includeTriggers = false,
        int $layerMask = -1,
    ): bool {
        Debug::error("[lenga_internal_rigidbody2d_is_grounded] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_rigidbody2d_get_contacts")) {
    function lenga_internal_rigidbody2d_get_contacts(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error("[lenga_internal_rigidbody2d_get_contacts] Function not implemented. ComponentId: $componentId");
        return [];
    }
}

if (!function_exists("lenga_internal_platform_effector2d_get_state")) {
    function lenga_internal_platform_effector2d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_platform_effector2d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_platform_effector2d_set_use_one_way")) {
    function lenga_internal_platform_effector2d_set_use_one_way(int $componentId, bool $value): bool
    {
        Debug::error("[lenga_internal_platform_effector2d_set_use_one_way] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_platform_effector2d_set_surface_buffer")) {
    function lenga_internal_platform_effector2d_set_surface_buffer(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_platform_effector2d_set_surface_buffer] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_area_effector2d_get_state")) {
    function lenga_internal_area_effector2d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_area_effector2d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_area_effector2d_set_use_global_angle")) {
    function lenga_internal_area_effector2d_set_use_global_angle(int $componentId, bool $value): bool
    {
        Debug::error("[lenga_internal_area_effector2d_set_use_global_angle] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_area_effector2d_set_force_angle")) {
    function lenga_internal_area_effector2d_set_force_angle(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_area_effector2d_set_force_angle] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_area_effector2d_set_force_magnitude")) {
    function lenga_internal_area_effector2d_set_force_magnitude(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_area_effector2d_set_force_magnitude] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_area_effector2d_set_drag")) {
    function lenga_internal_area_effector2d_set_drag(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_area_effector2d_set_drag] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_point_effector2d_get_state")) {
    function lenga_internal_point_effector2d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_point_effector2d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_point_effector2d_set_force_magnitude")) {
    function lenga_internal_point_effector2d_set_force_magnitude(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_point_effector2d_set_force_magnitude] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_point_effector2d_set_distance_scale")) {
    function lenga_internal_point_effector2d_set_distance_scale(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_point_effector2d_set_distance_scale] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_point_effector2d_set_inverse_squared")) {
    function lenga_internal_point_effector2d_set_inverse_squared(int $componentId, bool $value): bool
    {
        Debug::error("[lenga_internal_point_effector2d_set_inverse_squared] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_surface_effector2d_get_state")) {
    function lenga_internal_surface_effector2d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_surface_effector2d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_surface_effector2d_set_use_global_direction")) {
    function lenga_internal_surface_effector2d_set_use_global_direction(int $componentId, bool $value): bool
    {
        Debug::error("[lenga_internal_surface_effector2d_set_use_global_direction] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_surface_effector2d_set_speed")) {
    function lenga_internal_surface_effector2d_set_speed(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_surface_effector2d_set_speed] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_surface_effector2d_set_force_scale")) {
    function lenga_internal_surface_effector2d_set_force_scale(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_surface_effector2d_set_force_scale] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_buoyancy_effector2d_get_state")) {
    function lenga_internal_buoyancy_effector2d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_buoyancy_effector2d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_buoyancy_effector2d_set_density")) {
    function lenga_internal_buoyancy_effector2d_set_density(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_buoyancy_effector2d_set_density] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_buoyancy_effector2d_set_linear_drag")) {
    function lenga_internal_buoyancy_effector2d_set_linear_drag(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_buoyancy_effector2d_set_linear_drag] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_buoyancy_effector2d_set_flow_angle")) {
    function lenga_internal_buoyancy_effector2d_set_flow_angle(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_buoyancy_effector2d_set_flow_angle] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_buoyancy_effector2d_set_flow_magnitude")) {
    function lenga_internal_buoyancy_effector2d_set_flow_magnitude(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_buoyancy_effector2d_set_flow_magnitude] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_buoyancy_effector2d_set_surface_level")) {
    function lenga_internal_buoyancy_effector2d_set_surface_level(int $componentId, float $value): bool
    {
        Debug::error("[lenga_internal_buoyancy_effector2d_set_surface_level] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider2d_get_state")) {
    function lenga_internal_box_collider2d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_box_collider2d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider2d_set_size")) {
    function lenga_internal_box_collider2d_set_size(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_box_collider2d_set_size] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider2d_set_offset")) {
    function lenga_internal_box_collider2d_set_offset(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_box_collider2d_set_offset] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider2d_set_is_trigger")) {
    function lenga_internal_box_collider2d_set_is_trigger(int $componentId, bool $isTrigger): bool
    {
        Debug::error("[lenga_internal_box_collider2d_set_is_trigger] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider2d_set_material_path")) {
    function lenga_internal_box_collider2d_set_material_path(int $componentId, string $materialPath): bool
    {
        Debug::error("[lenga_internal_box_collider2d_set_material_path] Function not implemented. ComponentId: $componentId, MaterialPath: $materialPath");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider2d_set_material")) {
    function lenga_internal_box_collider2d_set_material(
        int $componentId,
        float $friction,
        float $bounciness,
        string $frictionCombine,
        string $bounceCombine,
    ): bool {
        Debug::error("[lenga_internal_box_collider2d_set_material] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider2d_is_touching")) {
    function lenga_internal_box_collider2d_is_touching(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): bool {
        Debug::error("[lenga_internal_box_collider2d_is_touching] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider2d_get_contacts")) {
    function lenga_internal_box_collider2d_get_contacts(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error("[lenga_internal_box_collider2d_get_contacts] Function not implemented. ComponentId: $componentId");
        return [];
    }
}

if (!function_exists("lenga_internal_box_collider3d_get_state")) {
    function lenga_internal_box_collider3d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_box_collider3d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider3d_set_size")) {
    function lenga_internal_box_collider3d_set_size(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_box_collider3d_set_size] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider3d_set_offset")) {
    function lenga_internal_box_collider3d_set_offset(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_box_collider3d_set_offset] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider3d_set_is_trigger")) {
    function lenga_internal_box_collider3d_set_is_trigger(int $componentId, bool $isTrigger): bool
    {
        Debug::error("[lenga_internal_box_collider3d_set_is_trigger] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider3d_is_touching")) {
    function lenga_internal_box_collider3d_is_touching(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): bool {
        Debug::error("[lenga_internal_box_collider3d_is_touching] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_box_collider3d_get_contacts")) {
    function lenga_internal_box_collider3d_get_contacts(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error("[lenga_internal_box_collider3d_get_contacts] Function not implemented. ComponentId: $componentId");
        return [];
    }
}

if (!function_exists("lenga_internal_capsule_collider3d_get_state")) {
    function lenga_internal_capsule_collider3d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_capsule_collider3d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_capsule_collider3d_set_radius")) {
    function lenga_internal_capsule_collider3d_set_radius(int $componentId, float $radius): bool
    {
        Debug::error("[lenga_internal_capsule_collider3d_set_radius] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_capsule_collider3d_set_height")) {
    function lenga_internal_capsule_collider3d_set_height(int $componentId, float $height): bool
    {
        Debug::error("[lenga_internal_capsule_collider3d_set_height] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_capsule_collider3d_set_offset")) {
    function lenga_internal_capsule_collider3d_set_offset(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_capsule_collider3d_set_offset] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_capsule_collider3d_set_is_trigger")) {
    function lenga_internal_capsule_collider3d_set_is_trigger(int $componentId, bool $isTrigger): bool
    {
        Debug::error("[lenga_internal_capsule_collider3d_set_is_trigger] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_capsule_collider3d_is_touching")) {
    function lenga_internal_capsule_collider3d_is_touching(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): bool {
        Debug::error("[lenga_internal_capsule_collider3d_is_touching] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_capsule_collider3d_get_contacts")) {
    function lenga_internal_capsule_collider3d_get_contacts(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error("[lenga_internal_capsule_collider3d_get_contacts] Function not implemented. ComponentId: $componentId");
        return [];
    }
}

if (!function_exists("lenga_internal_character_controller_get_state")) {
    function lenga_internal_character_controller_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_character_controller_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_character_controller_set_radius")) {
    function lenga_internal_character_controller_set_radius(int $componentId, float $radius): bool
    {
        Debug::error("[lenga_internal_character_controller_set_radius] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_character_controller_set_height")) {
    function lenga_internal_character_controller_set_height(int $componentId, float $height): bool
    {
        Debug::error("[lenga_internal_character_controller_set_height] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_character_controller_set_center")) {
    function lenga_internal_character_controller_set_center(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_character_controller_set_center] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_character_controller_set_skin_width")) {
    function lenga_internal_character_controller_set_skin_width(int $componentId, float $skinWidth): bool
    {
        Debug::error("[lenga_internal_character_controller_set_skin_width] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_character_controller_set_min_move_distance")) {
    function lenga_internal_character_controller_set_min_move_distance(int $componentId, float $minMoveDistance): bool
    {
        Debug::error("[lenga_internal_character_controller_set_min_move_distance] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_character_controller_set_detect_collisions")) {
    function lenga_internal_character_controller_set_detect_collisions(int $componentId, bool $detectCollisions): bool
    {
        Debug::error("[lenga_internal_character_controller_set_detect_collisions] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_character_controller_move")) {
    function lenga_internal_character_controller_move(int $componentId, float $x, float $y, float $z): int
    {
        Debug::error("[lenga_internal_character_controller_move] Function not implemented. ComponentId: $componentId");
        return 0;
    }
}

if (!function_exists("lenga_internal_character_controller_simple_move")) {
    function lenga_internal_character_controller_simple_move(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_character_controller_simple_move] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_circle_collider2d_get_state")) {
    function lenga_internal_circle_collider2d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_circle_collider2d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_circle_collider2d_set_radius")) {
    function lenga_internal_circle_collider2d_set_radius(int $componentId, float $radius): bool
    {
        Debug::error("[lenga_internal_circle_collider2d_set_radius] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_circle_collider2d_set_offset")) {
    function lenga_internal_circle_collider2d_set_offset(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_circle_collider2d_set_offset] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_circle_collider2d_set_is_trigger")) {
    function lenga_internal_circle_collider2d_set_is_trigger(int $componentId, bool $isTrigger): bool
    {
        Debug::error("[lenga_internal_circle_collider2d_set_is_trigger] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_circle_collider2d_set_material_path")) {
    function lenga_internal_circle_collider2d_set_material_path(int $componentId, string $materialPath): bool
    {
        Debug::error("[lenga_internal_circle_collider2d_set_material_path] Function not implemented. ComponentId: $componentId, MaterialPath: $materialPath");
        return false;
    }
}

if (!function_exists("lenga_internal_circle_collider2d_set_material")) {
    function lenga_internal_circle_collider2d_set_material(
        int $componentId,
        float $friction,
        float $bounciness,
        string $frictionCombine,
        string $bounceCombine,
    ): bool {
        Debug::error("[lenga_internal_circle_collider2d_set_material] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_circle_collider2d_is_touching")) {
    function lenga_internal_circle_collider2d_is_touching(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): bool {
        Debug::error("[lenga_internal_circle_collider2d_is_touching] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_circle_collider2d_get_contacts")) {
    function lenga_internal_circle_collider2d_get_contacts(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error("[lenga_internal_circle_collider2d_get_contacts] Function not implemented. ComponentId: $componentId");
        return [];
    }
}

if (!function_exists("lenga_internal_sphere_collider3d_get_state")) {
    function lenga_internal_sphere_collider3d_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_sphere_collider3d_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_collider3d_set_radius")) {
    function lenga_internal_sphere_collider3d_set_radius(int $componentId, float $radius): bool
    {
        Debug::error("[lenga_internal_sphere_collider3d_set_radius] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_collider3d_set_offset")) {
    function lenga_internal_sphere_collider3d_set_offset(int $componentId, float $x, float $y, float $z): bool
    {
        Debug::error("[lenga_internal_sphere_collider3d_set_offset] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_collider3d_set_is_trigger")) {
    function lenga_internal_sphere_collider3d_set_is_trigger(int $componentId, bool $isTrigger): bool
    {
        Debug::error("[lenga_internal_sphere_collider3d_set_is_trigger] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_collider3d_is_touching")) {
    function lenga_internal_sphere_collider3d_is_touching(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): bool {
        Debug::error("[lenga_internal_sphere_collider3d_is_touching] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sphere_collider3d_get_contacts")) {
    function lenga_internal_sphere_collider3d_get_contacts(
        int $componentId,
        bool $includeTriggers = true,
        int $layerMask = -1,
    ): array {
        Debug::error("[lenga_internal_sphere_collider3d_get_contacts] Function not implemented. ComponentId: $componentId");
        return [];
    }
}

if (!function_exists("lenga_internal_audio_source_get_state")) {
    function lenga_internal_audio_source_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_audio_source_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_audio_source_set_clip_path")) {
    function lenga_internal_audio_source_set_clip_path(int $componentId, string $clipPath): bool
    {
        Debug::error("[lenga_internal_audio_source_set_clip_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_audio_source_set_play_on_awake")) {
    function lenga_internal_audio_source_set_play_on_awake(int $componentId, bool $playOnAwake): bool
    {
        Debug::error("[lenga_internal_audio_source_set_play_on_awake] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_audio_source_set_loop")) {
    function lenga_internal_audio_source_set_loop(int $componentId, bool $loop): bool
    {
        Debug::error("[lenga_internal_audio_source_set_loop] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_audio_source_set_volume")) {
    function lenga_internal_audio_source_set_volume(int $componentId, float $volume): bool
    {
        Debug::error("[lenga_internal_audio_source_set_volume] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_audio_source_set_pitch")) {
    function lenga_internal_audio_source_set_pitch(int $componentId, float $pitch): bool
    {
        Debug::error("[lenga_internal_audio_source_set_pitch] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_audio_source_play")) {
    function lenga_internal_audio_source_play(int $componentId): bool
    {
        Debug::error("[lenga_internal_audio_source_play] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_audio_source_stop")) {
    function lenga_internal_audio_source_stop(int $componentId): bool
    {
        Debug::error("[lenga_internal_audio_source_stop] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_get_state")) {
    function lenga_internal_sprite_animation_get_state(int $componentId): array|false
    {
        Debug::error("[lenga_internal_sprite_animation_get_state] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_set_clip_path")) {
    function lenga_internal_sprite_animation_set_clip_path(int $componentId, string $clipPath): bool
    {
        Debug::error("[lenga_internal_sprite_animation_set_clip_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_set_controller_path")) {
    function lenga_internal_sprite_animation_set_controller_path(int $componentId, string $controllerPath): bool
    {
        Debug::error("[lenga_internal_sprite_animation_set_controller_path] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_set_state_name")) {
    function lenga_internal_sprite_animation_set_state_name(int $componentId, string $stateName): bool
    {
        Debug::error("[lenga_internal_sprite_animation_set_state_name] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_get_bool")) {
    function lenga_internal_sprite_animation_get_bool(int $componentId, string $parameterName): bool
    {
        Debug::error("[lenga_internal_sprite_animation_get_bool] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_set_bool")) {
    function lenga_internal_sprite_animation_set_bool(int $componentId, string $parameterName, bool $value): bool
    {
        Debug::error("[lenga_internal_sprite_animation_set_bool] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_get_int")) {
    function lenga_internal_sprite_animation_get_int(int $componentId, string $parameterName): int
    {
        Debug::error("[lenga_internal_sprite_animation_get_int] Function not implemented. ComponentId: $componentId");
        return 0;
    }
}

if (!function_exists("lenga_internal_sprite_animation_set_int")) {
    function lenga_internal_sprite_animation_set_int(int $componentId, string $parameterName, int $value): bool
    {
        Debug::error("[lenga_internal_sprite_animation_set_int] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_get_float")) {
    function lenga_internal_sprite_animation_get_float(int $componentId, string $parameterName): float
    {
        Debug::error("[lenga_internal_sprite_animation_get_float] Function not implemented. ComponentId: $componentId");
        return 0.0;
    }
}

if (!function_exists("lenga_internal_sprite_animation_set_float")) {
    function lenga_internal_sprite_animation_set_float(int $componentId, string $parameterName, float $value): bool
    {
        Debug::error("[lenga_internal_sprite_animation_set_float] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_set_trigger")) {
    function lenga_internal_sprite_animation_set_trigger(int $componentId, string $parameterName): bool
    {
        Debug::error("[lenga_internal_sprite_animation_set_trigger] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_reset_trigger")) {
    function lenga_internal_sprite_animation_reset_trigger(int $componentId, string $parameterName): bool
    {
        Debug::error("[lenga_internal_sprite_animation_reset_trigger] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_set_play_on_awake")) {
    function lenga_internal_sprite_animation_set_play_on_awake(int $componentId, bool $playOnAwake): bool
    {
        Debug::error("[lenga_internal_sprite_animation_set_play_on_awake] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_set_speed")) {
    function lenga_internal_sprite_animation_set_speed(int $componentId, float $speed): bool
    {
        Debug::error("[lenga_internal_sprite_animation_set_speed] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_play")) {
    function lenga_internal_sprite_animation_play(int $componentId): bool
    {
        Debug::error("[lenga_internal_sprite_animation_play] Function not implemented. ComponentId: $componentId");
        return false;
    }
}

if (!function_exists("lenga_internal_sprite_animation_stop")) {
    function lenga_internal_sprite_animation_stop(int $componentId): bool
    {
        Debug::error("[lenga_internal_sprite_animation_stop] Function not implemented. ComponentId: $componentId");
        return false;
    }
}
