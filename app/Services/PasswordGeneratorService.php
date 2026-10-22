<?php

namespace App\Services;

class PasswordGeneratorService
{
    protected const LOWERCASE = 'abcdefghjkmnpqrstuvwxyz'; // adashmaslik uchun o'xshash l, i, o olib tashlangan yoki to'liq saqlash mumkin
    protected const UPPERCASE = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    protected const NUMBERS   = '23456789';
    protected const SPECIALS  = '!@#$%^&*';

    // Standart to'liq alifbo (agar kerak bo'lsa)
    protected const FULL_LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
    protected const FULL_UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const FULL_NUMBERS   = '0123456789';
    protected const FULL_SPECIALS  = '!@#$%^&*';

    /**
     * Xavfsizlik talablariga 100% mos keluvchi parol generatsiya qilish:
     * • Kamida 8 ta belgi (standart: 10 ta belgi)
     * • Kamida bitta kichik harf (a-z)
     * • Kamida bitta katta harf (A-Z)
     * • Kamida bitta raqam (0-9)
     * • Kamida bitta maxsus belgi (!@#$%^&*)
     *
     * @param int $length Parol uzunligi (kamida 8)
     * @param bool $readable Foydalanuvchi adashmasligi uchun noaniq (l, 1, O, 0) belgilardan saqlanish
     * @return string
     */
    public function generate(int $length = 10, bool $readable = false): string
    {
        $length = max(8, $length);

        $lowercase = $readable ? self::LOWERCASE : self::FULL_LOWERCASE;
        $uppercase = $readable ? self::UPPERCASE : self::FULL_UPPERCASE;
        $numbers   = $readable ? self::NUMBERS : self::FULL_NUMBERS;
        $specials  = self::FULL_SPECIALS;

        $allChars = $lowercase . $uppercase . $numbers . $specials;

        // Har bir majburiy toifadan kamida bittadan belgi tanlaymiz (Kafolatlangan talablar)
        $passwordChars = [
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $specials[random_int(0, strlen($specials) - 1)],
        ];

        // Qolgan pozitsiyalarni barcha belgilar to'plamidan tasodifiy to'ldiramiz
        $remainingLength = $length - count($passwordChars);
        for ($i = 0; $i < $remainingLength; $i++) {
            $passwordChars[] = $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Cryptographically secure Fisher-Yates shuffle (tartibni xavfsiz aralashtirish)
        for ($i = count($passwordChars) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $temp = $passwordChars[$i];
            $passwordChars[$i] = $passwordChars[$j];
            $passwordChars[$j] = $temp;
        }

        $password = implode('', $passwordChars);

        // Har ehtimolga qarshi validatsiyadan o'tkazib kafolatlaymiz
        if (!$this->validate($password)) {
            return $this->generate($length, $readable);
        }

        return $password;
    }

    /**
     * Parol barcha xavfsizlik talablariga mosligini tekshirish
     */
    public function validate(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[a-z]/', $password)
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[!@#$%^&*]/', $password);
    }
}
