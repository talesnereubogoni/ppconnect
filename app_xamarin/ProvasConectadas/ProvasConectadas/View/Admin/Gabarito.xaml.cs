using ProvasConectadas.DB;
using ProvasConectadas.Model;
using ProvasConectadas.Model.Auxiliar;
using ProvasConectadas.View;
using ProvasConectadas.View.Admin;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;

namespace ProvasConectadas.View
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class Gabarito : ContentPage
    {
        private DadosDaProva dp;
        List<Questao> lq;
        int idprova; 
        public Gabarito(DadosDaProva dp, List<Questao> listadequestoes)
        {
            InitializeComponent();
            Database db = new Database();
            this.dp = dp;
            listaprovas.ItemsSource = listadequestoes;
            foreach(Questao q in listadequestoes)
            {
                if (q.tipodaquestao == DadosAdmin.QUESTAOFECHADA)
                    q.resposta_view = db.TextoDaAlternativa(q.respostafechada);
                else if (q.tipodaquestao == DadosAdmin.QUESTAOABERTA)
                    q.resposta_view = q.respostaaberta;
            }
            lq = listadequestoes;
            idprova = listadequestoes[0].idprova;
        }

        private void Bt_cancela_Clicked(object sender, EventArgs e)
        {
            Navigation.PopAsync();
        }


        private async void Bt_Finalizar_Clicked(object sender, EventArgs e)
        {
            Database db = new Database();
            Prova p = db.SelecionarProva(dp.idprovagerada);
//            p.usada = true;
            p.cpf = dp.cpfaluno;
            p.datarealizada = dp.data;
            p.nometutor = dp.nometutor;
            db.Atualizar(p);
            await DisplayAlert("Sucesso", "Prova finalizada", "OK");
            var confirma = await DisplayAlert("Enviar Dados", "Enviar dados da prova?", "Sim", "Não");
            if (confirma)
            {
                if (p != null)
                {
                    var lq = db.SelecionaQuestoes(p);
                    foreach (Questao qp in lq)
                    {
                        db.Deletar(qp);
                    }
                    db.Deletar(p);
                }                
            }
                    /*bool r = await WebService.WebService.AtualizarProvaRealizada(p);
                    if (r)
                    {
                        List<Questao> listadequestoes = db.SelecionaQuestoes(p);
                        foreach (Questao q in listadequestoes)
                        {
                            DadosDaQuestao dq = new DadosDaQuestao();
                            dq.id = q.idquestoes_da_prova_gerada;
                            dq.respostaaberta = q.respostaaberta;
                            dq.respostafechada = q.respostafechada;//db.AlternativaSelecionada(q);
                            dq.respostaletra = q.respostafechadaletra;
                            bool r1 = await WebService.WebService.AtualizarQuestaoRealizada(dq);
                            if (!r1)
                            {
                                await DisplayAlert("Erro", "Ocorreu algum erro na transmissão da questao", "ok");
                                erro = 2;
                            }
                            else
                            {
                                if (q.tipodaquestao == DadosAdmin.QUESTAOABERTA)
                                { // aberta
                                    AuxBlobString ab = new AuxBlobString { id = q.id, blob = Convert.ToBase64String(q.respostaimagem) };
                                    //DisplayAlert("img", ab.blob.Length.ToString(), "ok");
                                    var r2 = WebService.WebService.AtualizarQuestaoRealizadaImagem(ab);
                                    if (!r2.Result)
                                        erro = 3;
                                }
                                //resp_img.Source = ImageSource.FromStream(() => new MemoryStream(q.respostaimagem)); // exibe a imagem

                            }
                        }
                    }
                    else
                    {
                        erro = 1;
                    }
                    if (erro != 0)
                        await DisplayAlert("Erro", "Ocorreu algum erro na transmissão da prova " + erro.ToString(), "ok");
                    else
                    {
                        await DisplayAlert("Sucesso", "Prova enviada para o servidor", "OK");
                        /*
                        try
                        {
                            foreach (Questao q in lq)
                                db.Deletar(q); // deleta a questão e as alternativas
                            db.Deletar(p);
                        } catch (Exception)
                        {
                            await DisplayAlert("Erro", "Erro ao excluir os dados da prova", "OK");
                        }
                        
                    }
                }
            }*/
            await DisplayAlert("Fim", "Prova finalizada!", "Sair");
            System.Diagnostics.Process.GetCurrentProcess().Kill();
        }
    }
}