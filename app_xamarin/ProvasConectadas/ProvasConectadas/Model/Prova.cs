using System;
using System.Collections.Generic;
using System.Text;
using SQLite;


namespace ProvasConectadas.Model
{
    [Table("Provas")]
    public class Prova
    {
        [PrimaryKey]
        public int idProvaGerada { set; get; }
        public string cpf { set; get; }
        public string disciplina { set; get; }
        public DateTime datarecebida { set; get; }
        public DateTime datarealizada { set; get; }
        public DateTime dataenviada { set; get; }
        public bool usada { set; get; }
        public string nometutor { set; get; }
        public bool finalizada { set; get; }
        public int disciplinas_idDisciplinas { set; get; }
    }
}
