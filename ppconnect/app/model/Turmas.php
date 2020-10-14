<?php
/**
 * Turmas Active Record
 * @author  <your-name-here>
 */
class Turmas extends TRecord
{
    const TABLENAME = 'turmas';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $cursos;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('cursos_id');
    }

    
    /**
     * Method set_cursos
     * Sample of usage: $turmas->cursos = $object;
     * @param $object Instance of Cursos
     */
    public function set_cursos(Cursos $object)
    {
        $this->cursos = $object;
        $this->cursos_id = $object->id;
    }
    
    /**
     * Method get_cursos
     * Sample of usage: $turmas->cursos->attribute;
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
    


}
