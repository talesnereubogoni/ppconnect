using System;
using System.Collections.Generic;
using System.Text;
using SQLite;

namespace ProvasConectadas.Model
{
    //[Table("QuestoesDaProvaGerada")]
    public class QuestoesDaProvaGerada
    {
      //  [PrimaryKey, AutoIncrement]
        public int id { set; get; }
        public int idquestoes_da_prova_gerada { set; get; }
        public int provagerada_idProvaGerada { set; get; }
        public int numero_da_questao { set; get; }
        public int questoes_idQuestoes { set; get; }
        public int a { set; get; }
        public int b { set; get; }
        public int c { set; get; }
        public int d { set; get; }
        public int e { set; get; }
     
    }   
}
