<?php
/**
 * ProfessoresDaDisciplina Active Record
 * @author  <your-name-here>
 */
class ProfessoresDaDisciplina extends TRecord
{
    const TABLENAME = 'professores_da_disciplina';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $disciplinas;
    private $system_user;
    private $turmas;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('professor_id');
        parent::addAttribute('disciplinas_id');
        parent::addAttribute('turmas_id');
    }

    
    /**
     * Method set_disciplinas
     * Sample of usage: $professores_da_disciplina->disciplinas = $object;
     * @param $object Instance of Disciplinas
     */
    public function set_disciplinas(Disciplinas $object)
    {
        $this->disciplinas = $object;
        $this->disciplinas_id = $object->id;
    }
    
    /**
     * Method get_disciplinas
     * Sample of usage: $professores_da_disciplina->disciplinas->attribute;
     * @returns Disciplinas instance
     */
    public function get_disciplinas()
    {
        // loads the associated object
        if (empty($this->disciplinas))
            $this->disciplinas = new Disciplinas($this->disciplinas_id);
    
        // returns the associated object
        return $this->disciplinas;
    }
    
    
    /**
     * Method set_system_user
     * Sample of usage: $professores_da_disciplina->system_user = $object;
     * @param $object Instance of SystemUser
     */
    public function set_system_user(SystemUser $object)
    {
        $this->system_user = $object;
        $this->system_user_id = $object->id;
    }
    
    /**
     * Method get_system_user
     * Sample of usage: $professores_da_disciplina->system_user->attribute;
     * @returns SystemUser instance
     */
    public function get_system_user()
    {
        // loads the associated object
        if (empty($this->system_user))
            $this->system_user = new SystemUser($this->professor_id);
    
        // returns the associated object
        return $this->system_user;
    }
    

    /**
     * Method set_system_user
     * Sample of usage: $professores_da_disciplina->system_user = $object;
     * @param $object Instance of SystemUser
     */
    public function set_turmas(Turmas $object)
    {
        $this->turmas = $object;
        $this->turmas_id = $object->id;
    }
    
    /**
     * Method get_system_user
     * Sample of usage: $professores_da_disciplina->system_user->attribute;
     * @returns SystemUser instance
     */
    public function get_turmas()
    {
        // loads the associated object
        if (empty($this->turmas))
            $this->turmas = new Turmas($this->turmas_id);
    
        // returns the associated object
        return $this->turmas;
    }


}
