<?php
/**
 * Questoes Active Record
 * @author  <your-name-here>
 */
class Questoes extends TRecord
{
    const TABLENAME = 'questoes';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $system_user;
    private $disciplinas;
    private $questoes_tipos;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('professor_id');
        parent::addAttribute('disciplina_id');
        parent::addAttribute('questoes_tipos_id');
        parent::addAttribute('dificuldade');
        parent::addAttribute('data_criacao');
        parent::addAttribute('tags');
        parent::addAttribute('texto');
        parent::addAttribute('imagem');
        parent::addAttribute('audio');
        parent::addAttribute('video');
        parent::addAttribute('usada');
        parent::addAttribute('publica');
        parent::addAttribute('VF');
    }

    
    /**
     * Method set_system_user
     * Sample of usage: $questoes->system_user = $object;
     * @param $object Instance of SystemUser
     */
    public function set_system_user(SystemUser $object)
    {
        $this->system_user = $object;
        $this->system_user_id = $object->id;
    }
    
    /**
     * Method get_system_user
     * Sample of usage: $questoes->system_user->attribute;
     * @returns SystemUser instance
     */
    public function get_system_user()
    {
        // loads the associated object
        if (empty($this->system_user))
            $this->system_user = new SystemUser($this->system_user_id);
    
        // returns the associated object
        return $this->system_user;
    }
    
    
    /**
     * Method set_disciplinas
     * Sample of usage: $questoes->disciplinas = $object;
     * @param $object Instance of Disciplinas
     */
    public function set_disciplinas(Disciplinas $object)
    {
        $this->disciplinas = $object;
        $this->disciplinas_id = $object->id;
    }
    
    /**
     * Method get_disciplinas
     * Sample of usage: $questoes->disciplinas->attribute;
     * @returns Disciplinas instance
     */
    public function get_disciplinas()
    {
        // loads the associated object
        if (empty($this->disciplinas))
            $this->disciplinas = new Disciplinas($this->disciplina_id);
    
        // returns the associated object
        return $this->disciplinas;
    }
    
        /**
     * Method set_disciplinas
     * Sample of usage: $questoes->disciplinas = $object;
     * @param $object Instance of Disciplinas
     */
    public function set_questoes_tipos(QuestoesTipos $object)
    {
        $this->questoes_tipos = $object;
        $this->questoes_tipos_id = $object->id;
    }
    
    /**
     * Method get_disciplinas
     * Sample of usage: $questoes->disciplinas->attribute;
     * @returns Disciplinas instance
     */
    public function get_questoes_tipos()
    {
        // loads the associated object
        if (empty($this->questoes_tipos))
            $this->questoes_tipos = new QuestoesTipos($this->questoes_tipos_id);
    
        // returns the associated object
        return $this->questoes_tipos;
    }



}
