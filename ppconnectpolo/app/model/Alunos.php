<?php
/**
 * Alunos Active Record
 * @author  <your-name-here>
 */
class Alunos extends TRecord
{
    const TABLENAME = 'alunos';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $cursos;
    private $turmas;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cpf');
        parent::addAttribute('nome');
        parent::addAttribute('cursos_id');
        parent::addAttribute('turmas_id');
        parent::addAttribute('email');
        parent::addAttribute('telefone');
        parent::addAttribute('senha');
        parent::addAttribute('imagem');
        parent::addAttribute('digital');
        parent::addAttribute('voz');
        parent::addAttribute('atendimento_especial');
        parent::addAttribute('ativo');
    }

    
    /**
     * Method set_cursos
     * Sample of usage: $alunos->cursos = $object;
     * @param $object Instance of Cursos
     */
    public function set_cursos(Cursos $object)
    {
        $this->cursos = $object;
        $this->cursos_id = $object->id;
    }
    
    /**
     * Method get_cursos
     * Sample of usage: $alunos->cursos->attribute;
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
     * Sample of usage: $alunos->turmas = $object;
     * @param $object Instance of Turmas
     */
    public function set_turmas(Turmas $object)
    {
        $this->turmas = $object;
        $this->turmas_id = $object->id;
    }
    
    /**
     * Method get_turmas
     * Sample of usage: $alunos->turmas->attribute;
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
    


}
