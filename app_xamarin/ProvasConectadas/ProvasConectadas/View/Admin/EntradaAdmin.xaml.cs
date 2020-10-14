using ProvasConectadas.DB;
using ProvasConectadas.Model;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Security.Cryptography;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;

namespace ProvasConectadas.View.Admin
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class EntradaAdmin : ContentPage
    {

        public EntradaAdmin()
        {
            DadosAdmin.id = -1;
            DadosAdmin.tipousuario = -1;
            DadosAdmin.usuario = "";
            InitializeComponent();
            login.Focus();
        }

        private void Bt_sair_Clicked(object sender, EventArgs e)
        {
            DadosAdmin.id = -1;
            DadosAdmin.tipousuario = -1;
            DadosAdmin.usuario = "";
            Navigation.PopAsync();
        }

        /* Tipos de Usuário
         *    -1 = aluno
         *    0 = admin
         *    1 = tutor
         *    
         */    
        private void Bt_entrar_Clicked(object sender, EventArgs e)
        {            
            if (ValidaUsuario() && DadosAdmin.tipousuario==0)
            {
                    Navigation.PushAsync(new CrudConfig());
            }
            else
                DisplayAlert("Erro", "Usuário sem permissão!", "OK");
        }

        private bool ValidaUsuario()
        {
            if (String.IsNullOrEmpty(login.Text))
                return false;
            Database db = new Database();
            Tutor t = db.PesquisaTutor(login.Text);
            if (t != null)
            {
                string senhahash = MD5Hash(senha.Text);
                if (t.senha.Equals(senhahash))
                {
                    DadosAdmin.id = t.idUsuario;
                    DadosAdmin.tipousuario = t.tipousuario;
                    DadosAdmin.usuario = t.nome;
                    DadosAdmin.idcurso = t.idCurso;
                    return true;
                }
            }
            return false;
        }

        private string MD5Hash(string input)
        {
            StringBuilder hash = new StringBuilder();
            MD5CryptoServiceProvider md5provider = new MD5CryptoServiceProvider();
            byte[] bytes = md5provider.ComputeHash(new UTF8Encoding().GetBytes(input));

            for (int i = 0; i < bytes.Length; i++)
            {
                hash.Append(bytes[i].ToString("x2"));
            }
            return hash.ToString();
        }


    }
}