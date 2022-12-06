# Backend API
API beží na frameworku Lumen (9.1.5) (Laravel Components ^9.21). Framework už síce
nie je odporúčaný, ale pre toto zadanie som sa ho rozhodol aj tak zvoliť.
Pre spustenie je potrebne nainštalovať packages. Môžeme využiť manager composer
a nainštalovať príkazom "**composer install**". ~~Použitý server je vstavaný php server, 
vieme ho spustiť príkazom "**php -S localhost:{port} -t public/**" . Port môžeme použiť 
hocijaký voľný.~~ Pre spustenie servera som vyhotovil *docker image*, dôvod je ten, že vstavaný PHP
server nevie rozoznať *bodky* v URI.

Na vytvorenie potrebného image využijeme priložený *Dockerfile*. (Možná nutnosť využiť príkaz *sudo*,
záleží na konfigurácií systému)
```
# docker build -t {názov_image}
```

Po vyhotovení *docker image* vieme vytvoriť *docker container* následujúcim spôsobom
```
# docker run -d --name {ľubovoľný_názov_containera} -p {port_host_zariadenia}:80 {názov_image} .
```

Vysvetlivky myslím, že nie sú potrebné, ale prepínač -d spustí container v "detached" móde tzn. výpisy
nie su viditeľné v termináli, --name nastaví vlastný názov vytvoreného containera, -p priradí port,
port 80 je povinný, kvôli nastaveniam servera, následuje názov predvytvoreného *image*.

Druhá možnosť je pribalený súbor(image) s názvom **apiImage** rozbaliť a následne rovnako vytvoriť kontainer ako
v metóde vyššie s rozdielom, že názov *image* na konci bude apiImage.

Príkaz na rozbalenie *image*:

```
# docker load -i apiImage
```
---
Súborový systém som spravil tak, že koreňový priečinok sa nachádza v 
*storage/galleries*. Každý pridaný album vytvorí nový priečinok v priečinku *galleries*.
~~a podpriečinok *images* určený na ukladanie obrázkov.~~

- Zobrazenie zoznamu všetkých galérií:
```shell
GET http://localhost:{port}/gallery
```

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
DELETE http://localhost:{port}/{nazov_galerie}/{nazov_obrazka}
```

- Preview obrázka s konkrétnym rozlíšením, kde premenné w a h su šírka a výška, oddelené znakom x, {gallery} je názov galérie a premenná image na konci
je názov obrázka v danej galérii.
```shell
GET http://localhost:{port}/{w}x{h}/{gallery}/images/{image}
```
