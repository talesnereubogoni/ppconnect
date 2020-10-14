using System;
using System.Collections.Generic;
using System.Text;
using SQLite;
using Xamarin.Forms;
using Xamarin.Forms.Xaml;

using ProvasConectadas.Model;
using System.Linq;
using ProvasConectadas.View.Admin;
using ProvasConectadas.Model.Auxiliar;

namespace ProvasConectadas.DB
{
    public class Database
    {

        private SQLiteConnection conexao;
        string arquivo;
        public Database()
        {
            var dep = DependencyService.Get<I_Database>();
            arquivo = dep.getPath("ppconnect.sqlite");
            CriarBaseDeDados();
            Seed();
            conexao.Commit();
        }

        private void CriarBaseDeDados()
        {
            conexao = new SQLiteConnection(arquivo);
            conexao.CreateTable<Tutor>();
            conexao.CreateTable<Curso>();
            conexao.CreateTable<Prova>();
            conexao.CreateTable<Alternativas>();
            conexao.CreateTable<Aluno>();
            conexao.CreateTable<Configuracoes>();
            conexao.CreateTable<Questao>();
            conexao.CreateTable<DisciplinaProva>();
            conexao.CreateTable<QuestoesDaProvaGerada>();
        }

        //criar dados padrão
        public void Seed()
        {
            SeedTutor();
            List<int> temconf = conexao.Query<int>("SELECT COUNT(*) FROM Configuracoes");
            if (temconf[0] == 0) {
                Configuracoes c = new Configuracoes{ ip="", mac="", chave="", url="", senha=""};
                Inserir(c);                
            }
        }

        private void SeedTutor()
        {
            conexao.CreateTable<Tutor>();
            Tutor t = new Tutor{ idUsuario = 1, email = "admin", idCurso  = 0,
                            nome = "Administrador", senha = "21232f297a57a5a743894a0e4a801fc3", telefone= "",tipousuario=0};
            Inserir(t);                


        }

        public void ApagarTudo()
        {
            conexao.DropTable<Tutor>();
            conexao.DropTable<Curso>();
            conexao.DropTable<Alternativas>();
            conexao.DropTable<Aluno>();
            conexao.DropTable<Configuracoes>();
            conexao.DropTable<DisciplinaProva>();
            conexao.DropTable<Prova>();
            conexao.DropTable<Questao>();
            conexao.DropTable<QuestoesDaProvaGerada>();
            CriarBaseDeDados();
            Seed();
            conexao.Commit();
        }
        // inserir
        public void Inserir(Tutor p)
        {
            conexao.Insert(p);
            conexao.Commit();
        }
        public void Inserir(Curso p)
        {
            conexao.Insert(p);
            conexao.Commit();
        }
        public void Inserir(Prova p)
        {
            conexao.Insert(p);
            conexao.Commit();
        }

        public bool Inserir(Aluno a)
        {
            try
            {
                conexao.Insert(a);
                conexao.Commit();
                return true;
            }catch (Exception)
            {
                return false;
            }
        }
        public void Inserir(Questao q)
        {
            conexao.Insert(q);
            conexao.Commit();
        }
        public void Inserir(Alternativas a)
        {
            conexao.Insert(a);
            conexao.Commit();
        }
        public bool Inserir(DisciplinaProva d)
        {
            try
            {
                conexao.Insert(d);
                conexao.Commit();
                return true;
            } catch (Exception) { }
            return false;
        }

        public void Inserir(Configuracoes c)
        {
            conexao.Insert(c);
            conexao.Commit();
        }

        //deletar
        public void Deletar(Alternativas a)
        {
            conexao.Delete(a);
        }

        public void Deletar(Aluno a)
        {
            conexao.Delete(a);
        }
        public void Deletar(Tutor a)
        {
            conexao.Delete(a);
        }
        public void Deletar(Curso a)
        {
            conexao.Delete(a);
        }
        public void Deletar(DisciplinaProva a)
        {
            conexao.Delete(a);
        }
        public void Deletar(Questao q) { 
            conexao.Delete(q);
            conexao.Execute("DELETE FROM Alternativas WHERE idquestao = ?", q.id);
        }
        public void Deletar(Prova p) // não está completo
        {
            conexao.Execute("DELETE FROM Provas WHERE idProvaGerada = ?", p.idProvaGerada);
        }

        // pesquisas

        //retorna as configurações do dispositivo
        public Configuracoes PesquisaConf()
        {
            var vet = conexao.Table<Configuracoes>().ToList();
            try
            {
                return vet[0];
            }
            catch (Exception)
            {
                return null;
            }
        }

        //retorna o tutor com base em seu email/login
        public Tutor PesquisaTutor(string nome)
        {
            var vet = conexao.Table<Tutor>().Where(x => x.email.Equals(nome)).ToList();
            try
            {
                return vet[0];
            }
            catch (Exception)
            {
                return null;
            }
        }

        // retorna uma prova com base no seu id
        public Prova PesquisaProva(int id)
        {
            var vet =conexao.Table<Prova>().Where(x => x.idProvaGerada == id).ToList();
            try
            {
                return vet[0];
            } catch (Exception)
            {
                return null;
            }

        }

        //retorna a lista de provas do banco local
        public List <Prova> ConsultarProvas()
        {
            return conexao.Table<Prova>().ToList();
        }

        //retorna a lista de disciplinas do banco local
        public List<DisciplinaProva> ConsultarDisciplinas()
        {
            return conexao.Table<DisciplinaProva>().ToList();
        }

        //retorna true se existe uma prova para a disciplina que não foi finalizada
        public bool ExisteProvaDaDisciplina(DisciplinaProva d)
        {
            //cria uma lista com as provas da disciplina que não foram finalizadas
            try
            {
                var aux = conexao.Table<Prova>().Where(x => x.disciplinas_idDisciplinas == d.idDisciplinas &&
                                                       x.finalizada == false).ToList();
                if (aux.Count == 0)
                    return false;
            } catch (Exception)
            {
                return false;
            }
            return true;
        }

        //retorna a lista de alternaticas de uma questão
        public List <Alternativas> SelecionaAlternativas(Questao q)
        {    
            return conexao.Table<Alternativas>().Where( x => x.idquestao == q.idquestoes_da_prova_gerada).ToList();
        }

        //retorna uma alternativa com base no seu id
        public Questao SelecionarQuestao (int id)
        {
            return conexao.Table<Questao>().Where(x => x.id == id).First();
        }

        public int SelecionarCursoDoTutor(int idtutor)
        {
            return conexao.Table<Tutor>().Where(x => x.idUsuario== DadosAdmin.id).First().idCurso;
        }

        //retorna a lista de tutores
        public List<Tutor> SelecionarTutores()
        {
            return conexao.Table<Tutor>().Where(x => x.tipousuario == DadosAdmin.TUTORES).ToList();
        }

        //retorna a lista de cursos
        public List<Curso> SelecionarCursos()
        {
            return conexao.Table<Curso>().ToList();
        }

        //retorna as provas não usadas que estão no dispositivo
        public List<Prova> SelecionarProvasAbertas()
        {
            var lista = conexao.Table<Prova>().Where(x => x.usada == false).ToList();
            return lista;
        }

        //retorna o curso pelo id
        public Curso ConsultaCurso(int id)
        {
            return conexao.Table<Curso>().Where(x => x.idCurso == id).First() ;
        }



        // retorna a lista de questões de uma prova
        public List<Questao> SelecionaQuestoes(Prova p)
        {
            List<Questao> lq = conexao.Table<Questao>().Where(l => l.idprova == p.idProvaGerada).OrderBy(l => l.numero).ToList();
            foreach (Questao q in lq)
            {
                if (string.IsNullOrEmpty(q.respostaaberta + q.respostafechadaletra))
                    q.color = "Red";
                else
                {
                    q.color = "Green";
                    q.resposta_view = q.respostafechadaletra + q.respostaaberta;
                }
            }
            return lq;
        }

        //retorna uma prova de uma disciplina pelo seu nome da disciplina
        public Prova SelecionarProva(string disciplina)
        {
            List<Prova> lp = conexao.Table<Prova>().ToList();
            foreach (var p in lp)
            {
                if (p.disciplina.Equals(disciplina) && !p.usada)
                {
                    return p;
                }
            }
            return null;
        }

        //retorna a primeira prova com a id da disciplina
        public Prova SelecionaProvaDaDisciplina(int id)
        {
            return conexao.Table<Prova>().Where(x => x.disciplinas_idDisciplinas == id).First();
        }


        //retorna uma prova pelo seu id
        public Prova SelecionarProva(int id)
        {
            try
            {
                return conexao.Table<Prova>().Where(x => x.idProvaGerada == id).First();
            }
            catch (Exception)
            {
                return null;
            }
        }

        public List<Prova> SelecionarStatusProvas()
        {
            return conexao.Table<Prova>().ToList();
        }

        private string GetNomeCurso(int idc)
        {
//            return "Sem Curso Definido";
            List<Curso> c = conexao.Table<Curso>().Where(x => x.idCurso == idc).ToList();
            if (c != null)
            {
                try
                {
                    return c[0].nome;
                }
                catch (Exception) { }
            }
            return "Sem Curso Definido";
        }

        private Prova ProvaDaDisciplina(int iddisciplina)
        {
            try
            {
                var aux = conexao.Table<Prova>().Where(x => x.disciplinas_idDisciplinas.Equals(iddisciplina)).ToList();
                return aux[0];
            } catch (Exception) {
                return null;
            }
        }
        
        public List<DisciplinaProvas> ListaDiscipliasProvas(int idcurso)
        {
            List<DisciplinaProvas> lista = new List<DisciplinaProvas>();
            List<DisciplinaProva> disc = conexao.Table<DisciplinaProva>().Where(x => x.curso_id.Equals(idcurso)).ToList();
            foreach(DisciplinaProva d in disc)
            {
                DisciplinaProvas dp = new DisciplinaProvas();
                dp.disciplina = d.nome;
                dp.idDisciplina = d.idDisciplinas;
                dp.idCurso = d.curso_id;
                dp.curso = GetNomeCurso(d.curso_id);
                Prova p = ProvaDaDisciplina(d.idDisciplinas);
                if (p == null)// não tem prova
                {
                    dp.idProva = -1;
                    dp.data = " ";
                    dp.provausada = "Sem Prova";
                    dp.img = "bt_download.png";
                } else
                {
                    if(p.datarealizada.Year==1)
                        dp.data = p.datarecebida.ToString("G");
                    else
                        dp.data = p.datarealizada.ToString("G");
                    if (p.usada)
                    {
                        dp.provausada = "Sim";
                        dp.img = "upload_icon.png";
                    }
                    else
                    {
                        dp.idProva = p.idProvaGerada;
                        dp.provausada = "Não";
                        dp.img = "bt_apagar.png";
                    }
                }
                lista.Add(dp);
            }
            return lista;
        }


        // atualização de dados no banco de dados
        public void Atualizar(Questao q) // questão
        {
            conexao.Update(q);
            conexao.Commit();
        }
        public void Atualizar(Prova p)
        {
            conexao.Update(p);
            conexao.Commit();
        }

        public void Atualizar(Configuracoes c)
        {
            conexao.Execute("UPDATE Configuracoes set ip = ?, url = ?, chave =?, " +
                "senha = ?, mac = ?", c.ip, c.url, c.chave, c.senha, c.mac);
            conexao.Commit();
        }

        public void ApagarTutores()
        {
            conexao.DropTable<Tutor>();
            SeedTutor();
        }

        public void ApagarCursos()
        {
            conexao.DropTable<Curso>();
            conexao.CreateTable<Curso>();
        }

        //Retorna uma disciplina com base nos eu ID
        public DisciplinaProva ConsultaDisciplina (int id)
        {
            try
            {
                var aux = conexao.Table<DisciplinaProva>().Where(x => x.idDisciplinas.Equals(id)).First();
                return aux;
            }
            catch (Exception)
            {
                return null;
            }
        }

        public string TextoDaAlternativa(int idalternativa)
        {
            return conexao.Table<Alternativas>().Where(x => x.id.Equals(idalternativa)).First().texto;
        }

        public Aluno PesquisarAluno(string cpf)
        {
            return conexao.Table<Aluno>().Where(x => x.cpf.Equals(cpf)).First();
        }

    }
}
