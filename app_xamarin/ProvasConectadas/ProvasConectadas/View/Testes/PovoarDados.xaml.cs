using ProvasConectadas.DB;
using ProvasConectadas.Model;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;
using ProvasConectadas.WebService;
using System.Collections.ObjectModel;

namespace ProvasConectadas.View.Testes
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class PovoarDados : ContentPage
    {
        public PovoarDados()
        {
            InitializeComponent();
        }

        /*
        private void Bt_Provas_Clicked(object sender, EventArgs e)
        {
            try
            {
                Database db = new Database();
                Prova p = new Prova();
                p.idprova = 1;
                p.disciplina = "Matemática";
                p.datarecebida = DateTime.Now;
                db.Inserir(p);
                p.idprova = 2;
                p.disciplina = "Português";
                p.datarecebida = DateTime.Now;
                db.Inserir(p);
                p.idprova = 3;
                p.disciplina = "Estruturas de Dados II";
                p.datarecebida = DateTime.Now;
                db.Inserir(p);
                DisplayAlert("Sucesso", "Provas Salvas", "OK");
            } catch (Exception)
            {
                DisplayAlert("Erro", "Ocorreu um erro ao salvar os dados da prova", "OK");
            }
        }
        */
        public byte[] ReadFully(Stream input)
        {
            using (MemoryStream ms = new MemoryStream())
            {
                input.CopyTo(ms);
                return ms.ToArray();
            }
        }

        private byte[] ConverteImagemParaByte(Stream input)
        {
            byte[] buffer = new byte[16 * 1024];
            using (MemoryStream ms = new MemoryStream())
            {
                int read;
                while ((read = input.Read(buffer, 0, buffer.Length)) > 0)
                {
                    ms.Write(buffer, 0, read);
                }
                return ms.ToArray();
            }
        }

        private void Bt_Questoes_Clicked(object sender, EventArgs e)
        {
            try
            {
                Database db = new Database();
                ObservableCollection<Questao> dl = WebService.WebService.BuscarQuestoesDaDisciplina("Portugues");
                foreach (Questao q in dl)
                {
                    Resultado.Text += q.id+ ", ";
                    db.Inserir(q);
                }
                DisplayAlert("Sucesso", "Questões inseridas", "OK");
            }
            catch (Exception)
            {
                DisplayAlert("Erro", "Ocorreu um erro ao carregar as questoes", "OK");
            }
                  
        }

        private void Bt_Apagar_Clicked(object sender, EventArgs e)
        {
            try
            {
                Database db = new Database();
                db.ApagarTudo();
                DisplayAlert("Sucesso", "Dados Apagados", "OK");
            }
            catch (Exception) {
                DisplayAlert("Erro", "Ocorreu um erro ao apagar os dados", "OK");
            };
        }
        /*
        private void Bt_Carregar_Questao_Clicked(object sender, EventArgs e)
        {
            int idQuestao = 111;
            Questao q = WebService.WebService.BuscarQuestao(idQuestao);
            AuxBlob blob = WebService.WebService.BuscarQuestaoImagem(idQuestao.ToString());
            q.urlimagem = blob.blob;
            Database db = new Database();
            db.Inserir(q);

            /*Resultado.Text = string.Format("id: {0}\n" +
                "Enunciado: {1}\n" +
                "Tipo: {2}\n", q.id, q.enunciado, q.tipodaquestao);
            String datastr = BitConverter.ToString(q.urlimagem); // converte para imagem
            img.Source = ImageSource.FromStream(() => new MemoryStream(q.urlimagem)); // exibe a imagem
            
        }
    */

            /*
        //Cria provas com os nomes das disciplinas, isso depois deve ser substituído pelas provas disponíveis no servidor
        private void Bt_Disciplinas_Clicked(object sender, EventArgs e)
        {
            try
            {
                Database db = new Database();
                List<Disciplina> dl = WebService.WebService.BuscarDisciplinasComProva();
                int pr = 1;
                Prova p = new Prova();
                foreach (Disciplina d in dl)
                {
                    //p.idprova = pr++;
                    p.disciplina = d.nome;
                    p.datarecebida = DateTime.Now;
                    p.usada = false;
                    Resultado.Text += p.disciplina;
                    db.Inserir(p);
                }
                DisplayAlert("Sucesso", "Disciplinas ok", "OK");
            }
            catch (Exception)
            {
                DisplayAlert("Erro", "Ocorreu um erro ao carregar as disciplinas", "OK");
            }
        }
        */

//Carrega uma questão das disciplinas que estão no banco de dados local
        private void Bt_Carregar_Questao_Clicked_1(object sender, EventArgs e)
        {
            /*
            Database db = new Database();
            List <Prova> listadeprovas = db.ConsultarProvas();
            for(int i=0; i< listadeprovas.Count; i++)
            {
                if (!listadeprovas[i].usada) // não tem prova carregada
                { // carregar uma prova
                    Prova p = WebService.WebService.CarregaUmaProva(listadeprovas[i]);
                    listadeprovas[i].idProvaGerada = p.idProvaGerada;
                    listadeprovas[i].datarecebida = DateTime.Now;
                    db.Atualizar(listadeprovas[i]);
                    // carregas as questões da prova gerada
                    
                    ObservableCollection<QuestoesDaProvaGerada> listadequestoes = WebService.WebService.BuscarQuestoesProvaGerada(p.idProvaGerada);
                    Resultado.Text += p.idProvaGerada + " "+ listadequestoes.Count+ " - ";
                    try
                    {
                        foreach (QuestoesDaProvaGerada qp in listadequestoes)
                        {
                            Questao q = WebService.WebService.BuscarQuestao(qp.questoes_idQuestoes);
                            //q.disciplina = listadeprovas[i].disciplina;
                            q.idprova = qp.provagerada_idProvaGerada;
                            q.numero = qp.numero_da_questao;
                            q.idquestoes_da_prova_gerada = qp.idquestoes_da_prova_gerada;
                            
                            AuxBlob blob = WebService.WebService.BuscarImagemDaQuestao(qp.questoes_idQuestoes);
                            q.urlimagem = blob.blob;

                            db.Inserir(q);
                            Alternativas a = WebService.WebService.BuscarAlternativa(qp.a);
                            a.letra = "A";
                            a.idquestao = q.id;
                            blob = WebService.WebService.BuscarImagemDaAlternativa(a.id);
                            a.urlimagem = blob.blob;
                            

                            Alternativas b = WebService.WebService.BuscarAlternativa(qp.b);
                            b.letra = "B";
                            b.idquestao = q.id;
                            blob = WebService.WebService.BuscarImagemDaAlternativa(b.id);
                            b.urlimagem = blob.blob;

                            Alternativas c = WebService.WebService.BuscarAlternativa(qp.c);
                            c.letra = "C";
                            c.idquestao = q.id;
                            blob = WebService.WebService.BuscarImagemDaAlternativa(c.id);
                            c.urlimagem = blob.blob;

                            Alternativas d = WebService.WebService.BuscarAlternativa(qp.d);
                            d.letra = "D";
                            d.idquestao = q.id;
                            blob = WebService.WebService.BuscarImagemDaAlternativa(d.id);
                            d.urlimagem = blob.blob;

                            Alternativas e1 = WebService.WebService.BuscarAlternativa(qp.e);
                            e1.letra = "E";
                            e1.idquestao = q.id;
                            blob = WebService.WebService.BuscarImagemDaAlternativa(e1.id);
                            e1.urlimagem = blob.blob;

                            db.Inserir(a);
                            db.Inserir(b);
                            db.Inserir(c);
                            db.Inserir(d);
                            db.Inserir(e1);

                           // db.Inserir(qp);

                            Resultado.Text += qp.numero_da_questao + " ";
                        }
                    }catch (Exception) { }
                    
                }
            }
/*            string idQuestao = "111";
            Questao q = WebService.WebService.BuscarQuestao(idQuestao);
            AuxBlob blob = WebService.WebService.BuscarQuestaoImagem(idQuestao);
            q.urlimagem = blob.blob;
            Database db = new Database();
            db.Inserir(q);
            */
        }


    }


}