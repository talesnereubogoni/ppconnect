using ProvasConectadas.DB;
using ProvasConectadas.Model;
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
    public partial class IniciarProva : ContentPage
    {
        Prova prova = new Prova();
        Aluno aluno = new Aluno();
        public IniciarProva(Prova p)
        {
 
            InitializeComponent();
            this.prova = p;
            Database db = new Database();
            this.aluno = db.PesquisarAluno(DadosAdmin.usuario);
            Title = p.disciplina;
            nomedoaluno.Text = aluno.nome;
            cpf.Text = aluno.cpf;
            nomedadisciplina.Text = prova.disciplina;
            //DisplayAlert("TesteIniciarProva", "Chegou aqui", "ok");
        }

        private void Bt_confirma_Clicked(object sender, EventArgs e)
        {
            prova.datarealizada = DateTime.Now;
            prova.nometutor = aluno.nometutor;
            App.Current.MainPage = new NavigationPage(new QuestoesDaProva(prova));
        }

        private async void Bt_cancela_Clicked(object sender, EventArgs e)
        {
            await Navigation.PopAsync();
        }
    }
}