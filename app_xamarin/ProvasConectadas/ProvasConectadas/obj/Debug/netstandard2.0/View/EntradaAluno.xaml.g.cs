//------------------------------------------------------------------------------
// <auto-generated>
//     O código foi gerado por uma ferramenta.
//     Versão de Tempo de Execução:4.0.30319.42000
//
//     As alterações ao arquivo poderão causar comportamento incorreto e serão perdidas se
//     o código for gerado novamente.
// </auto-generated>
//------------------------------------------------------------------------------

[assembly: global::Xamarin.Forms.Xaml.XamlResourceIdAttribute("ProvasConectadas.View.EntradaAluno.xaml", "View/EntradaAluno.xaml", typeof(global::ProvasConectadas.View.EntradaAluno))]

namespace ProvasConectadas.View {
    
    
    [global::Xamarin.Forms.Xaml.XamlFilePathAttribute("View\\EntradaAluno.xaml")]
    public partial class EntradaAluno : global::Xamarin.Forms.ContentPage {
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private global::Xamarin.Forms.Picker Pk_cursos;
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private global::Xamarin.Forms.Picker Pk_provas;
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private global::Xamarin.Forms.StackLayout Sl_dados;
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private global::Xamarin.Forms.Entry cpf;
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private global::Xamarin.Forms.Entry nome;
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private global::Xamarin.Forms.Entry email;
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private global::Xamarin.Forms.Button bt_imagem;
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private global::Xamarin.Forms.Image imagem;
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private global::Xamarin.Forms.ImageButton bt_aluno;
        
        [global::System.CodeDom.Compiler.GeneratedCodeAttribute("Xamarin.Forms.Build.Tasks.XamlG", "2.0.0.0")]
        private void InitializeComponent() {
            global::Xamarin.Forms.Xaml.Extensions.LoadFromXaml(this, typeof(EntradaAluno));
            Pk_cursos = global::Xamarin.Forms.NameScopeExtensions.FindByName<global::Xamarin.Forms.Picker>(this, "Pk_cursos");
            Pk_provas = global::Xamarin.Forms.NameScopeExtensions.FindByName<global::Xamarin.Forms.Picker>(this, "Pk_provas");
            Sl_dados = global::Xamarin.Forms.NameScopeExtensions.FindByName<global::Xamarin.Forms.StackLayout>(this, "Sl_dados");
            cpf = global::Xamarin.Forms.NameScopeExtensions.FindByName<global::Xamarin.Forms.Entry>(this, "cpf");
            nome = global::Xamarin.Forms.NameScopeExtensions.FindByName<global::Xamarin.Forms.Entry>(this, "nome");
            email = global::Xamarin.Forms.NameScopeExtensions.FindByName<global::Xamarin.Forms.Entry>(this, "email");
            bt_imagem = global::Xamarin.Forms.NameScopeExtensions.FindByName<global::Xamarin.Forms.Button>(this, "bt_imagem");
            imagem = global::Xamarin.Forms.NameScopeExtensions.FindByName<global::Xamarin.Forms.Image>(this, "imagem");
            bt_aluno = global::Xamarin.Forms.NameScopeExtensions.FindByName<global::Xamarin.Forms.ImageButton>(this, "bt_aluno");
        }
    }
}