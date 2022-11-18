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


- Vytvorenie novej galérie - POST http://localhost:{port}/gallery/{nazov_galerie}


- Zoznam obrázkov danej galérie - GET http://localhost:{port}/gallery/{nazov_galerie}


- Nahranie obrázka do galérie - POST http://localhost:{port}/gallery/{nazov_galerie} .. 
potrebné nastaviť form-data key na image


- Vymazanie konktrétnej galérie - DELETE http://localhost:{port}/{nazov_galerie}


- Vymazanie konkrétneho obrázka - DELETE http://localhost:{port}/{nazov_galerie}/images/{nazov_obrazka}
