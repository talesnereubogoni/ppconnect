<?php
/**
 * DisciplinasDoCurso Active Record
 * @author  <your-name-here>
 */
class DisciplinasDoCurso extends TRecord
{
    const TABLENAME = 'disciplinas_do_curso';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $cursos;
    private $disciplinas;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('disciplinas_id');
        parent::addAttribute('curso_id');
    }

    
    /**
     * Method set_cursos
     * Sample of usage: $disciplinas_do_curso->cursos = $object;
     * @param $object Instance of Cursos
     */
    public function set_cursos(Cursos $object)
    {
        $this->cursos = $object;
        $this->cursos_id = $object->id;
    }
    
    /**
     * Method get_cursos
     * Sample of usage: $disciplinas_do_curso->cursos->attribute;
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
     * Method set_disciplinas
     * Sample of usage: $disciplinas_do_curso->disciplinas = $object;
     * @param $object Instance of Disciplinas
     */
    public function set_disciplinas(Disciplinas $object)
    {
        $this->disciplinas = $object;
        $this->disciplinas_id = $object->id;
    }
    
    /**
     * Method get_disciplinas
     * Sample of usage: $disciplinas_do_curso->disciplinas->attribute;
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
    


}
