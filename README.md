# Backend API

API beží na frameworku Lumen (9.1.5) (Laravel Components ^9.21). Framework už síce
nie je odporúčaný, ale pre toto zadanie som sa ho rozhodol aj tak zvoliť.
Pre spustenie je potrebne nainštalovať packages. Môžeme využiť manager composer
a nainštalovať príkazom "**composer install**". Použitý server je vstavaný php server, 
vieme ho spustiť príkazom "**php -S localhost:{port} -t public/**" . Port môžeme použiť 
hocijaký voľný.

Súborový systém som spravil tak, že koreňový priečinok sa nachádza v 
*storage/galleries*. Každý pridaný album vytvorí nový priečinok v priečinku *galleries*
a podpriečinok *images* určený na ukladanie obrázkov.

- Zobrazenie zoznamu všetkých galérií - GET http://localhost:{port}/gallery


- Vytvorenie novej galérie:  
```shell
POST http://localhost:{port}/gallery/{nazov_galerie}
```

- Zoznam obrázkov danej galérie:
```shell
GET http://localhost:{port}/gallery/{nazov_galerie}
```

- Nahranie obrázka do galérie, potrebné nastaviť *form-data key* na *image*:


```shell
POST http://localhost:{port}/gallery/{nazov_galerie}
```

- Vymazanie konktrétnej galérie:
```shell
DELETE http://localhost:{port}/{nazov_galerie}
```

- Vymazanie konkrétneho obrázka:
```shell
DELETE http://localhost:{port}/{nazov_galerie}/images/{nazov_obrazka}
```

- Preview obrázka s konkrétnym rozlíšením, kde premenné w a h su šírka a výška, oddelené znakom x, {gallery} je názov galérie a premenná image na konci
je názov obrázka v danej galérii **bez** rozšírenia (extension)
```shell
GET http://localhost:{port}/images/{w}x{h}/{gallery}/images/{image}
```
