## Front

- [ ] Logo in SVG (Flo)
- [ ] Navbar User = ["Home", "My Recipes", "My Profile", "Logout"]
- [ ] Navbar Admin = ["Home", "Recipes", "Categories", "Profiles", "Logout"]
- [ ] Navbar Visitor = ["Home", "Sign In / Sign Up"]
- [ ] Home => Liste des recipes du site:
    - [ ] Bienvenue "${pseudo}"
    - [ ] Div Filtres => Choix de catégorie(s)
    - [ ] Recipes => Card (Avec Like et bouton partager) / Titre et image en cover sur la div
    - [ ] Onclick => Redirection vers page recette.php?id_recette=tbd
    - [ ] Footer => Avec logo + Links + Copyright
- [ ] My Recipes => Liste des recipes de l'utilisateur:
    - [ ] Recipes => Card (Bouton partager) / Titre et image en cover sur la div
    - [ ] Add recipe ( Modal avec form )
    - [ ] Buttons => ["Delete", "Edit"]
    - [ ] Footer => Avec logo + Links + Copyright
- [ ] Categories => Liste des catégories (Get / Add / Edit / Delete)
- [ ] My Profile => Patch Pwd / Nombre de recettes / Nombre de comment / Nombre de likes reçus au global / Rond (Avec initiales) / Bienvenue "${pseudo}"
- [ ] Profiles => Liste des Profiles / Get / Patch / Delete
- [ ] Recipes => Liste des Profiles / Get / Patch / Delete
- [ ] Sign In page => Form de connexion ( pseudo, pwd)
- [ ] Sign Up  => Form d'inscription( pseudo, pwd)
- [ ] Logout => Session Destroy

## Back Flo

- [x] Endpoint User
- [x] GET / GET by Id
- [x] POST 
- [x] DELETE ALL / DELETE by Id
- [x] PATCH by Id
- [x] Utilisation d'ORM (Object Relational Mapping) -> https://symfony.com/doc/current/doctrine.html
- [x] Docker Compose / DockerFile
- [x] Swagger
- [x] Endpoint Authent

## Back Manue

- [x] Endpoint Comment
- [x] GET / GET by Id
- [ ] POST 
- [ ] DELETE ALL / DELETE by Id
- [ ] PATCH by Id
- [ ] Utilisation d'ORM (Object Relational Mapping) -> https://symfony.com/doc/current/doctrine.html
- [x] CRUD complet pour Category avec name en clé primaire (actions par name et pas par Id) => impossible de mettre name en clef primaire...


## Back Adam

- [x] Endpoint Recipe
- [x] GET / GET by ID
- [x] POST
- [x] DELETE ALL / DELETE by ID
- [x] PATCH by id
- [x] Utilisation d'ORM (Object Relational Mapping) -> https://symfony.com/doc/current/doctrine.html
- [x] Swagger