## Comment utiliser le scss

Exemple :

```bash
  ├── layout.scss
  ├── base
  │   ├── _index.scss
  │   ├── _b.animation.scss
  │   └── _b.core.scss
  ├── components
  │   ├── _index.scss
  │   ├── _c.banner.scss
  │   ├── _c.card.scss
  ├── layout
  │   ├── _index.scss
  │   ├── _l.dashboard.scss
  ├── particles
  │   ├── _index.scss
  │   ├── _p.bubble.scss
  ├── settings
  │   ├── _index.scss
  │   ├── _s.color.scss
  └── utilities
      ├── _index.scss
      ├── _u.layout.scss
      └── _u.text.scss
```

le `layout.scss` est le fichier qui va se charger d'importer tous les dossiers `scss`.
Viendront ensuit les `_index.scss` qui se chargeront à leur tour d'importer les `scss` du dossier.
Par convention on mettera un underscore `_` devant chaque ficher et le fichier commencera par la/les première(s) lettres du nom du dossier.

@matthieudou
