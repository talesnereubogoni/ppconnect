<?php
/**
 * TurmasDoPolo Active Record
 * @author  <your-name-here>
 */
class TurmasDoPolo extends TRecord
{
    const TABLENAME = 'turmas_do_polo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $cursos;
    private $turmas;
    private $tutor;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('turmas_id');
        parent::addAttribute('polos_id');
        parent::addAttribute('tutor_id');
    }

    
    /**
     * Method set_cursos
     * Sample of usage: $turmas_do_polo->cursos = $object;
     * @param $object Instance of Cursos
     */
    public function set_cursos(Cursos $object)
    {
        $this->cursos = $object;
        $this->cursos_id = $object->id;
    }
    
    /**
     * Method get_cursos
     * Sample of usage: $turmas_do_polo->cursos->attribute;
     * @returns Cursos instance
     */
    public function get_cursos()
    {
        // loads the associated object
        if (empty($this->cursos))
            $this->cursos = new Cursos($this->cursos_id);
    
        // returns the associated object
        return $this->cursos;
    }
    
    
    /**
     * Method set_turmas
     * Sample of usage: $turmas_do_polo->turmas = $object;
     * @param $object Instance of Turmas
     */
    public function set_turmas(Turmas $object)
    {
        $this->turmas = $object;
        $this->turmas_id = $object->id;
    }
    
    /**
     * Method get_turmas
     * Sample of usage: $turmas_do_polo->turmas->attribute;
     * @returns Turmas instance
     */
    public function get_turmas()
    {
        // loads the associated object
        if (empty($this->turmas))
            $this->turmas = new Turmas($this->turmas_id);
    
        // returns the associated object
        return $this->turmas;
    }
    
    /**
     * Method set_turmas
     * Sample of usage: $turmas_do_polo->turmas = $object;
     * @param $object Instance of Turmas
     */
    public function set_tutor(SystemUser $object)
    {
        $this->tutor = $object;
        $this->tutor_id = $object->id;
    }
    
    /**
     * Method get_turmas
     * Sample of usage: $turmas_do_polo->turmas->attribute;
     * @returns Turmas instance
     */
    public function get_tutor()
    {
        // loads the associated object
        if (empty($this->tutor))
            $this->tutor = new SystemUser($this->tutor_id);
    
        // returns the associated object
        return $this->tutor;
    }

}
