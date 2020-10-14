using ProvasConectadas.DB;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;

using Plugin.Toast;
using Plugin.Toast.Abstractions;
using ProvasConectadas.Model;
using System.Collections.ObjectModel;
using Newtonsoft.Json;

namespace ProvasConectadas.View.Admin
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class Administracao : ContentPage
    {
        ToastLength toastLength = ToastLength.Short;
        public Administracao()
        {
            InitializeComponent();
        }

        private void Bt_Carregar_Prova_Clicked(object sender, EventArgs e)
        {
            /*
            Database db = new Database();
            List<Disciplina> listadedisciplinas = db.ConsultarDisciplinas();
            Prova p = new Prova();
            Resultado.Text = "Teste";
            foreach (Disciplina d in listadedisciplinas)
            {
                Resultado.Text += d.idDisciplinas.ToString();
                if (!db.ExisteProvaDaDisciplina(d))
                {
                    Resultado.Text += d.idDisciplinas.ToString() + d.nome;
                    //criar a prova
                    try
                    {
                        string Conteudo= WebService.WebService.CarregaUmaProvaString(d);
                        ObservableCollection<Prova> p1 = JsonConvert.DeserializeObject<ObservableCollection<Prova>>(Conteudo);
                        DisplayAlert("Prova Carregada", Conteudo +d.idDisciplinas.ToString(), "ok");
                        p = p1[0];
                        p.cpf = "";
                        p.datarecebida = DateTime.Now;
                        p.disciplina = d.nome;
                        p.usada = false;
                        db.Inserir(p);

                    }
                    catch (Exception)
                    {
                        DisplayAlert("Erro", "Erro ao carregar prova", "OK");
                    }

                    try
                    {
                        ObservableCollection<QuestoesDaProvaGerada> listadequestoes = WebService.WebService.BuscarQuestoesProvaGerada(p.idProvaGerada);
                    //                DisplayAlert("Atenção",s, "ok");

                        foreach (QuestoesDaProvaGerada qp in listadequestoes)
                        {
                            string s = WebService.WebService.BuscarQuestaoString(qp.questoes_idQuestoes);
                            Questao q = JsonConvert.DeserializeObject<Questao>(s);
                            //DisplayAlert("ErroQuestao", qp.questoes_idQuestoes.ToString()+s, "ok");
                            //Questao q = new Questao();
                            AuxBlob blob = new AuxBlob();
                            q = WebService.WebService.BuscarQuestao(qp.questoes_idQuestoes);
//                            q.disciplina = p.disciplina;
                            q.idprova = qp.provagerada_idProvaGerada;
                            q.numero = qp.numero_da_questao;
                            q.idquestoes_da_prova_gerada = qp.idquestoes_da_prova_gerada;
                            Resultado.Text += q.numero.ToString();
                            q.urlimagem = WebService.WebService.BuscarImagemDaQuestao(qp.questoes_idQuestoes).blob;
                            q.urlvideo = WebService.WebService.BuscarVideoDaQuestao(qp.questoes_idQuestoes).blob;
                            q.urlaudio = WebService.WebService.BuscarAudioDaQuestao(qp.questoes_idQuestoes).blob;

                            db.Inserir(q);
                            // carregar as alternativas
                            if (q.tipodaquestao == 1)
                            { // multipla escolha
                                try
                                {
                                    Alternativas a = WebService.WebService.BuscarAlternativa(qp.a);
                                    a.letra = "A";
                                    a.idquestao = q.id;
                                    a.urlimagem = WebService.WebService.BuscarImagemDaAlternativa(a.id).blob;
                                    a.urlvideo = WebService.WebService.BuscarVideoDaAlternativa(a.id).blob;
                                    a.urlaudio = WebService.WebService.BuscarAudioDaAlternativa(a.id).blob;
                                    
                                    Alternativas b = WebService.WebService.BuscarAlternativa(qp.b);
                                    b.letra = "B";
                                    b.idquestao = q.id;
                                    b.urlimagem = WebService.WebService.BuscarImagemDaAlternativa(b.id).blob;
                                    b.urlvideo = WebService.WebService.BuscarVideoDaAlternativa(b.id).blob;
                                    b.urlaudio = WebService.WebService.BuscarAudioDaAlternativa(b.id).blob;

                                    Alternativas c = WebService.WebService.BuscarAlternativa(qp.c);
                                    c.letra = "C";
                                    c.idquestao = q.id;
                                    c.urlimagem = WebService.WebService.BuscarImagemDaAlternativa(c.id).blob;
                                    c.urlvideo = WebService.WebService.BuscarVideoDaAlternativa(c.id).blob;
                                    c.urlaudio = WebService.WebService.BuscarAudioDaAlternativa(c.id).blob;

                                    Alternativas dd = WebService.WebService.BuscarAlternativa(qp.d);
                                    dd.letra = "D";
                                    dd.idquestao = q.id;
                                    dd.urlimagem = WebService.WebService.BuscarImagemDaAlternativa(dd.id).blob;
                                    dd.urlvideo = WebService.WebService.BuscarVideoDaAlternativa(dd.id).blob;
                                    dd.urlaudio = WebService.WebService.BuscarAudioDaAlternativa(dd.id).blob; ;

                                    Alternativas ee = WebService.WebService.BuscarAlternativa(qp.e);
                                    ee.letra = "E";
                                    ee.idquestao = q.id;
                                    ee.urlimagem = WebService.WebService.BuscarImagemDaAlternativa(ee.id).blob;
                                    ee.urlvideo = WebService.WebService.BuscarVideoDaAlternativa(ee.id).blob;
                                    ee.urlaudio = WebService.WebService.BuscarAudioDaAlternativa(ee.id).blob;

                                    db.Inserir(a);
                                    db.Inserir(b);
                                    db.Inserir(c);
                                    db.Inserir(dd);
                                    db.Inserir(ee);
                                }
                                catch (Exception)
                                {
                                    DisplayAlert("Erro", "Erro ao carregar as alternativas", "OK");
                                }
                            }
                        }   
                    }
                    catch (Exception) {
                        DisplayAlert("Erro", "Erro ao carregar as Questões", "OK");
                    }
                } else
                {
                    DisplayAlert("Erro", "Não foram encontradas provas no servidor", "OK");
                }
            }
            CrossToastPopUp.Current.ShowToastSuccess("Alternativas prova carregada", toastLength);
            */
        }

        //carrega as disciplinas
       /* private void Bt_Disciplinas_Clicked(object sender, EventArgs e)
        {
            try
            {
                Database db = new Database();
                List<Disciplina> dl = WebService.WebService.BuscarDisciplinasComProva();
                if (dl == null)
                    DisplayAlert("Erro", "Não existem disciplinas cadastradas", "OK");
                else
                {
                    Disciplina disciplina = new Disciplina();
                    foreach (Disciplina d in dl)
                    {
                        db.Inserir(d);
                        Resultado.Text += d.nome;
                    }
                    CrossToastPopUp.Current.ShowToastSuccess("Disciplinas Carregadas", toastLength);
                }
            }
            catch (Exception)
            {
                DisplayAlert("Erro", "Ocorreu um erro ao carregar as disciplinas", "OK");
            }
        }
        */
        //limpa o banco de dados
        private void Bt_Apagar_Clicked(object sender, EventArgs e)
        {
            try
            {
                Database db = new Database();
                db.ApagarTudo();
                CrossToastPopUp.Current.ShowToastSuccess("Dados Apagados", toastLength);
                //DisplayAlert("Sucesso", "Dados Apagados", "OK");
            }
            catch (Exception)
            {
                DisplayAlert("Erro", "Ocorreu um erro aoapagar os dados", "OK");
            };
        }
    }
}