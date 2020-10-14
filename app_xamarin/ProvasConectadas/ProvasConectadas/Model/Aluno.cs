using System;
using System.Collections.Generic;
using System.Text;
using SQLite;

namespace ProvasConectadas.Model
{
    [Table("Alunos")]
    public class Aluno
    {
        [PrimaryKey]
        public string cpf { set; get; }
        public string nome { set; get; }
        public string email { set; get; }
        public string nometutor { set; get; }
        public byte[] audio { set; get; }
        public byte[] imagem { set; get; }
        public float latitude { set; get; }
        public float longitude { set; get; }
    }
}
