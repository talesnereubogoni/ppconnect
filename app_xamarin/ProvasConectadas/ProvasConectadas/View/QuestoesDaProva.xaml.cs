using ProvasConectadas.DB;
using ProvasConectadas.Model;
using ProvasConectadas.Model.Auxiliar;
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
    public partial class QuestoesDaProva : ContentPage
    {
        List<Questao> listadequestoes = new List<Questao>() ;
        Prova prova = new Prova();
        string cpfdoaluno;
        public QuestoesDaProva(Prova p)
        {
            prova = p;
            //cpf.Text = cpfaluno;
            cpfdoaluno = Admin.DadosAdmin.usuario; 
            InitializeComponent();
            Title = "Prova de " + prova.disciplina;
            SelecionarQuestoesDaProva();
        }

        public void SelecionarQuestoesDaProva()
        {
            Database db = new Database();
            listadequestoes = db.SelecionaQuestoes(prova);
            listaprovas.ItemsSource = listadequestoes;
        }

        private async void Bt_resp_Clicked(object sender, EventArgs e)
        {
            ImageButton button = (ImageButton)sender;
            string numero = button.CommandParameter.ToString();
            await Navigation.PushAsync(new QuestaoView(numero, prova));
        }

        private async void Bt_finalizar_Clicked(object sender, EventArgs e)
        {
            bool resposta = true;
            //verificar se todas as perguntas estão com respostas
            bool todas = true;
            foreach(Questao q in listadequestoes)
            {
                if (string.IsNullOrEmpty(q.respostafechadaletra) && q.tipodaquestao == 1) // questão aberta
                    todas = false;
                else
                   if (string.IsNullOrEmpty(q.respostaaberta) && q.tipodaquestao == 4) // questão aberta
                       todas = false;
            }
            if (!todas)
            {
                await DisplayAlert("Finalizar a prova", "Atenção! Existem questões não respondidas!", "Retornar");
                await Navigation.PopAsync();
            }
            else
            {
                DadosDaProva dp = new DadosDaProva();
                dp.data = DateTime.Now;
                dp.nometutor = prova.nometutor;
                dp.idprovagerada = prova.idProvaGerada;
                dp.cpfaluno = cpfdoaluno;
                await Navigation.PushAsync(new Gabarito(dp, listadequestoes));
            }
        }
    }
}