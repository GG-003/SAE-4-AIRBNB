# Pistes d'améliorations
- Réduire le nombre de requêtes SQL
- Optimiser les requêtes SQL
- Optimiser la structure de la base de données
- Ajouter des systèmes de cache

# Comment lancer le projet
1. Décompressez l'archive `src/assets/images.zip` pour avoir un dossier `src/assets/images`.
2. Lancez Docker Deskop
3. Lancer le projet avec la commande `docker compose up -d` (pour arrêter le projet `docker compose stop`)
4. Ouvrez PHPMyAdmin sur `http://localhost:8080` et importez le fichier `./database.sql.gz` dans la base de données `tp`.
5. Dans Docker Desktop, accédez au terminal du container `backend` et lancez la commande `composer install`
Ouvrez l'application sur http://localhost

Si l'un des ports est déjà utilisé par votre pc, modifiez le fichier `docker-compose.yml` en changeant les valeurs **à gauche des `:`** dans la liste des `ports` des `services` (la partie à gauche correspond aux ports de votre machine et à droite ceux auxquels ils sont liés dans le container).