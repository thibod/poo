<?php
class PersonnagesRepository
{
  private $_db; // Instance de PDO
  
  public function __construct($db)
  {
    $this->setDb($db);
  }
  
  public function add(Personnage $perso)
  {
    $q = $this->_db->prepare('INSERT INTO personnages(nom) VALUES(:nom)'); // Préparation de la requête d'insertion.
    $q->bindValue(':nom', $perso->nom()); // Assignation des valeurs pour le nom du personnage.
    $q->execute(); // Exécution de la requête.
    
    // Hydratation du personnage passé en paramètre avec assignation de son identifiant et des dégâts initiaux (= 0).
    $perso->hydrate([
        'id' => $this->_db->lastInsertId(),
        'degats' => 0,
      ]);
  }
  
  public function count()
  {
    // Exécute une requête COUNT() et retourne le nombre de résultats retournés.
    return $this->_db->query('SELECT COUNT(*) FROM personnages')->fetchColumn();
  }
  
  public function delete(Personnage $perso)
  {
    $this->_db->exec('DELETE FROM personnages WHERE id = '.$perso->id()); // Exécute une requête de type DELETE.
  }
  
  public function exists($info)
  {
    if (is_int($info)) // Si le paramètre est un entier, c'est qu'on a fourni un identifiant.
    { // On exécute alors une requête COUNT() avec une clause WHERE, et on retourne un boolean.
        $q = $this->_db->query('SELECT id, nom, degats FROM personnages WHERE id = '.$info);
        $donnees = $q->fetch(PDO::FETCH_ASSOC);
        
        return new Personnage($donnees);
    }
    
    else // Sinon c'est qu'on a passé un nom.
    { // Exécution d'une requête COUNT() avec une clause WHERE, et retourne un boolean.
      $q = $this->_db->prepare('SELECT id, nom, degats FROM personnages WHERE nom = :nom');
      $q->execute([':nom' => $info]);
    
      return new Personnage($q->fetch(PDO::FETCH_ASSOC));
    }
  }
  
  public function get($info)
  {
    if (is_int($info)) // Si le paramètre est un entier, on veut récupérer le personnage avec son identifiant.
    { // Exécute une requête de type SELECT avec une clause WHERE, et retourne un objet Personnage.

        $q = $this->_db->query('SELECT id, nom, degats FROM personnages WHERE id = '.$info);
        $donnees = $q->fetch(PDO::FETCH_ASSOC);
        
        return new Personnage($donnees);
    }
    
    else  // Sinon, on veut récupérer le personnage avec son nom.
    { // Exécute une requête de type SELECT avec une clause WHERE, et retourne un objet Personnage.
      $q = $this->_db->prepare('SELECT id, nom, degats FROM personnages WHERE nom = :nom');
      $q->execute([':nom' => $info]);
    
      return new Personnage($q->fetch(PDO::FETCH_ASSOC));
    }
  }
  
  public function getList($nom)
  {
    $persos = [];
    // Retourne la liste des personnages dont le nom n'est pas $nom.
    
    $q = $this->_db->prepare('SELECT id, nom, degats FROM personnages WHERE nom <> :nom ORDER BY nom');
    $q->execute([':nom' => $nom]);
    
    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $persos[] = new Personnage($donnees);
    }
    
    return $persos;
    // Le résultat sera un tableau d'instances de Personnage.
  }
  
  public function update(Personnage $perso)
  {
    $q = $this->_db->prepare('UPDATE personnages SET degats = :degats WHERE id = :id'); // Prépare une requête de type UPDATE.
    // Assignation des valeurs à la requête.
    $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
    $q->bindValue(':id', $perso->id(), PDO::PARAM_INT);
    $q->execute(); // Exécution de la requête.  
  }
  
  public function setDb(PDO $db)
  {
    $this->_db = $db;
  }
}