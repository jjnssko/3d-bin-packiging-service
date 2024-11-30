# TODO List

## 1. Nastavení prostředí
- [x] Vytvořit projekt a nastavit Composer
- [x] Přidat potřebné závislosti:
    - Doctrine ORM
    - Guzzle (pro HTTP requesty)
    - Dotenv (pro správu .env souborů)
- [x] Nastavit Docker prostředí (pro PHP, MySQL a Adminer)
- [x] Konfigurovat .env a .env.local pro API přístup

## 2. Entita a Repozitář
- [x] Vytvořit Doctrine entitu pro `PackagingResult` (pro ukládání výsledků balení)
    - Definovat vlastnosti jako `inputHash`, `boxes`, atd.
    - Použít `Doctrine\DBAL\Types\Types` pro typy sloupců
- [x] Vytvořit repozitář pro entitu `PackagingResult` (metody jako `getByInputHash()`, `save()` atd.)

## 3. Práce s Externím API (3D Bin Packing)
- [x] Vytvořit třídu `Packing3dBinClient` pro komunikaci s API
    - Implementovat metody pro volání API (s využitím Guzzle)
    - Implementovat kontrolu, že hodnoty `username` a `api_key` existují v `.env`
- [x] Ošetřit rate limit API - uložit výsledky do databáze pro opětovné použití

## 4. Validace a Výpočet
- [ ] Implementovat metodu pro výpočet nejvhodnějšího boxu
    - Použít API pro výpočet, nebo fallback na jednoduchý výpočet při výpadku API
    - Validovat vstupy (produkty a konfigurace boxů)

## 5. Práce s Doctrine Cache
- [ ] Implementovat cache pro výsledky výpočtu (uložení do databáze)
    - Zajistit, že se nevolá API pro stejné vstupy dvakrát
    - Použít Doctrine ORM pro uložení a načítání dat

## 6. Testování
- [ ] Napsat základní testy pro kontrolu základní funkcionality
    - Testy pro ukládání a načítání dat z databáze
    - Testy pro komunikaci s API (mockování API odpovědí)
- [ ] Otestovat chování aplikace při výpadku externího API

## 7. Optimalizace a Zabezpečení
- [ ] Ošetřit všechny výjimky a chyby při práci s API
- [ ] Zabezpečit přístup k API klíčům a citlivým údajům v `.env`
- [ ] Zajistit, že kód je testovatelný a snadno udržovatelný

## 8. Dokumentace
- [ ] Doplňte dokumentaci k API (jak funguje volání API, formát vstupů a výstupů)
- [ ] Doplňte dokumentaci k metodám v repozitářích a službách
- [ ] Přidat README soubor k projektu s instrukcemi pro nastavení a použití