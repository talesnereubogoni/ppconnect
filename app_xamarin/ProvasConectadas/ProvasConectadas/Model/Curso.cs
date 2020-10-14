using System;
using System.Collections.Generic;
using System.Text;
using SQLite;

namespace ProvasConectadas.Model
{
    [Table("Cursos")]
    public class Curso
    {
        public int idCurso { get; set; }
        public string nome { get; set; }
        public string codigodeacesso { get; set; }

    }
}
