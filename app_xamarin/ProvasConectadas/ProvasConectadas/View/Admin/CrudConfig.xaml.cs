using Plugin.Toast;
using Plugin.Toast.Abstractions;
using ProvasConectadas.DB;
using ProvasConectadas.Model;
using ProvasConectadas.View.Admin.CadastroCurso;
using ProvasConectadas.View.Admin.CadastroTutor;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;

namespace ProvasConectadas.View.Admin
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class CrudConfig : ContentPage
    {
        private Configuracoes conf { set; get; }
        public CrudConfig()
        {
            InitializeComponent();
            //carrega os dados da configuração atual
            Database db = new Database();
            conf = db.PesquisaConf();
            if (conf != null)
            {
                ip.Text = conf.ip;
                url.Text = conf.url;
                codigo.Text = conf.mac;
                chave.Text = conf.chave;
                DadosAdmin.mac = conf.mac;
            }
            ip.IsEnabled = false;
            url.IsEnabled = false;
            codigo.IsEnabled = false;
            chave.IsEnabled = false;
            bt_salvar.IsEnabled = false;
        }

        private async void Bt_apagardados_Clicked(object sender, EventArgs e)
        {
            var resposta =  await DisplayAlert("Confirmação", "Atenção! Todos os dados serão excluídos.", "Confirmar", "Cancelar");
            if (resposta)
            {
                Database db = new Database();
                db.ApagarTudo();
                CrossToastPopUp.Current.ShowToastSuccess("Banco de Dados Apagado", ToastLength.Short);
            }
        }

        private void Bt_crudtutor_Clicked(object sender, EventArgs e)
        {
            Navigation.PushAsync(new ListaTutor());
        }

        private void Bt_crudcursos_Clicked(object sender, EventArgs e)
        {
            Navigation.PushAsync(new ListaCurso());
        }

        private void Bt_sair_Clicked(object sender, EventArgs e)
        {
            Navigation.PopAsync();
        }

        private void Bt_salvar_Clicked(object sender, EventArgs e)
        {
            ip.IsEnabled = false;
            url.IsEnabled = false;
            codigo.IsEnabled = false;
            bt_senha.IsEnabled = false;
            chave.IsEnabled = false;
            conf.ip = ip.Text;
            conf.url = url.Text;
            conf.mac = codigo.Text;
            conf.chave = chave.Text;
            Database db = new Database();
            db.Atualizar(conf);
            bt_crudcursos.IsEnabled = true;
            bt_crudtutor.IsEnabled = true;
            bt_apagardados.IsEnabled = true;
            bt_salvar.IsEnabled = false;
            bt_editardados.IsEnabled = true;
            DadosAdmin.mac = conf.mac;
            DadosAdmin.conexao = conf.url;
        }

        private void Bt_editardados_Clicked(object sender, EventArgs e)
        {
            if (conf == null)
                conf = new Configuracoes();
            ip.IsEnabled = true;
            url.IsEnabled = true;
            codigo.IsEnabled = true;
            chave.IsEnabled = true;
            bt_senha.IsEnabled = true;
            bt_editardados.IsEnabled = false;
            bt_crudcursos.IsEnabled = false;
            bt_crudtutor.IsEnabled = false;
            bt_apagardados.IsEnabled = false;
            bt_salvar.IsEnabled = true;
            ip.Focus();
        }

        private async void Bt_senha_Clicked(object sender, EventArgs e)
        {
            if (bt_senha.IsEnabled)
            {
               // bt_senha.Text = "Validar";
                PainelSenha.IsVisible = true;
                senhaatual.Text = "";
                senha0.Text = "";
                senha1.Text = "";
                senhaatual.Focus();
                bt_salvar.IsEnabled = false;
            }
            else
            {
                if (!String.IsNullOrEmpty(senhaatual.Text))
                {
                    if (senha0.Text.Equals(senha1.Text) && (senhaatual.Text.Equals(conf.senha)
                       || String.IsNullOrEmpty(conf.senha)))
                    {
                        conf.senha = senha0.Text;
                    }
                    else
                    {
                        var resp = await DisplayAlert("Erro", "Senhas não conferem!", "Sair", "Tentar Novamente");
                        if (resp)
                        {
                            bt_salvar.IsEnabled = true;
//                            bt_senha.Text = "Senha";
                            PainelSenha.IsVisible = false;
                        }
                    }
                }
                else
                {
                    var resp = await DisplayAlert("Erro", "Senha inválida!", "Sair", "Tentar Novamente");
                    if (resp)
                    {
                        bt_salvar.IsEnabled = true;
 //                       bt_senha.Text = "Senha";
                        PainelSenha.IsVisible = false;
                    }
                }
            }
        }
    }
}