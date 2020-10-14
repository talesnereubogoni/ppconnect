using System;
using System.Collections.Generic;
using System.Text;
using SQLite;

namespace ProvasConectadas.Model
{
    [Table("Disciplinas")]
    public class DisciplinaProva
    { 
        [PrimaryKey]
        public int idDisciplinas { set; get; }
        public string nome { set; get; }
        public int curso_id { set; get; }
        public int tutor_id { set; get; }
    }
}
